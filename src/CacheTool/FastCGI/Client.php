<?php
/**
 * Note : Code is released under the GNU LGPL
 *
 * Please do not change the header of this file
 *
 * This library is free software; you can redistribute it and/or modify it under the terms of the GNU
 * Lesser General Public License as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * See the GNU Lesser General Public License for more details.
 */

namespace CacheTool\FastCGI;

/**
 * Handles communication with a FastCGI application
 *
 * @author      Pierrick Charron <pierrick@webstart.fr>
 * @author      Daniel Aharon <dan@danielaharon.com>
 * @author      Erik Bernhardson <bernhardsonerik@gmail.com>
 * @version     2.0
 */
class Client
{
    const VERSION_1            = 1;

    const BEGIN_REQUEST        = 1;
    const ABORT_REQUEST        = 2;
    const END_REQUEST          = 3;
    const PARAMS               = 4;
    const STDIN                = 5;
    const STDOUT               = 6;
    const STDERR               = 7;
    const DATA                 = 8;
    const GET_VALUES           = 9;
    const GET_VALUES_RESULT    = 10;
    const UNKNOWN_TYPE         = 11;
    const MAXTYPE              = self::UNKNOWN_TYPE;

    const RESPONDER            = 1;
    const AUTHORIZER           = 2;
    const FILTER               = 3;

    const REQUEST_COMPLETE     = 0;
    const CANT_MPX_CONN        = 1;
    const OVERLOADED           = 2;
    const UNKNOWN_ROLE         = 3;

    const MAX_CONNS            = 'MAX_CONNS';
    const MAX_REQS             = 'MAX_REQS';
    const MPXS_CONNS           = 'MPXS_CONNS';

    const HEADER_LEN           = 8;

    /**
     * Socket
     * @var Resource
     */
    protected $sock = null;

    /**
     * Host
     * @var String
     */
    protected $host = null;

    /**
     * Port
     * @var Integer
     */
    protected $port = null;

    /**
     * Unix socket path
     * @var string
     */
    protected $socketPath = null;

    /**
     * Keep Alive
     * @var Boolean
     */
    protected $keepAlive = false;

    /**
     * A request has been sent.
     * @var Boolean
     */
    protected $awaitingResponse = false;

    /**
     * Constructor
     *
     * @param String $host Host of the FastCGI application or path to the FastCGI unix socket
     * @param Integer $port Port of the FastCGI application or null for the FastCGI unix socket
     */
    public function __construct($host, $port = null)
    {
        if ($port !== null) {
            $this->host = $host;
            $this->port = $port;
        } else {
            $this->socketPath = $host;
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->sock) {
            socket_close($this->sock);
        }
    }

    /**
     * Define whether or not the FastCGI application should keep the connection
     * alive at the end of a request
     *
     * @param Boolean $b true if the connection should stay alive, false otherwise
     */
    public function setKeepAlive($b)
    {
        $this->keepAlive = (boolean)$b;
        if (!$this->keepAlive && $this->sock) {
            $this->close();
        }
    }

    /**
     * Get the keep alive status
     *
     * @return Boolean true if the connection should stay alive, false otherwise
     */
    public function getKeepAlive()
    {
        return $this->keepAlive;
    }

    /**
     * Close the fastcgi connection
     */
    public function close()
    {
        if ($this->sock) {
            socket_close($this->sock);
            $this->sock = null;
        }
    }

    /**
     * Create a connection to the FastCGI application
     */
    protected function connect()
    {
        if (!$this->sock) {
            if ($this->socketPath !== null) {
                $this->sock = @socket_create(AF_UNIX, SOCK_STREAM, 0);
                $address = $this->socketPath;
                $port = 0;
            } else {
                $this->sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                $address = $this->host;
                $port = $this->port;
            }
            if (!$this->sock) {
                throw CommunicationException::socketCreate();
            }
            if (false === @socket_connect($this->sock, $address, $port)) {
                throw CommunicationException::socketConnect($this->sock, $address, $port);
            }
        }
    }

    /**
     * Build a FastCGI packet
     *
     * @param Integer $type Type of the packet
     * @param String $content Content of the packet
     * @param Integer $requestId RequestId
     */
    protected function buildPacket($type, $content, $requestId = 1)
    {
        $clen = strlen($content);
        return chr(self::VERSION_1)         /* version */
            . chr($type)                    /* type */
            . chr(($requestId >> 8) & 0xFF) /* requestIdB1 */
            . chr($requestId & 0xFF)        /* requestIdB0 */
            . chr(($clen >> 8 ) & 0xFF)     /* contentLengthB1 */
            . chr($clen & 0xFF)             /* contentLengthB0 */
            . chr(0)                        /* paddingLength */
            . chr(0)                        /* reserved */
            . $content;                     /* content */
    }

    /**
     * Build an FastCGI Name value pair
     *
     * @param String $name Name
     * @param String $value Value
     * @return String FastCGI Name value pair
     */
    protected function buildNvpair($name, $value)
    {
        $nlen = strlen($name);
        $vlen = strlen($value);
        if ($nlen < 128) {
            /* nameLengthB0 */
            $nvpair = chr($nlen);
        } else {
            /* nameLengthB3 & nameLengthB2 & nameLengthB1 & nameLengthB0 */
            $nvpair = chr(($nlen >> 24) | 0x80) . chr(($nlen >> 16) & 0xFF) . chr(($nlen >> 8) & 0xFF) . chr($nlen & 0xFF);
        }
        if ($vlen < 128) {
            /* valueLengthB0 */
            $nvpair .= chr($vlen);
        } else {
            /* valueLengthB3 & valueLengthB2 & valueLengthB1 & valueLengthB0 */
            $nvpair .= chr(($vlen >> 24) | 0x80) . chr(($vlen >> 16) & 0xFF) . chr(($vlen >> 8) & 0xFF) . chr($vlen & 0xFF);
        }
        /* nameData & valueData */
        return $nvpair . $name . $value;
    }

    /**
     * Read a set of FastCGI Name value pairs
     *
     * @param String $data Data containing the set of FastCGI NVPair
     * @return array of NVPair
     */
    protected function readNvpair($data, $length = null)
    {
        $array = array();

        if ($length === null) {
            $length = strlen($data);
        }

        $p = 0;

        while ($p != $length) {

            $nlen = ord($data{$p++});
            if ($nlen >= 128) {
                $nlen = ($nlen & 0x7F << 24);
                $nlen |= (ord($data{$p++}) << 16);
                $nlen |= (ord($data{$p++}) << 8);
                $nlen |= (ord($data{$p++}));
            }
            $vlen = ord($data{$p++});
            if ($vlen >= 128) {
                $vlen = ($nlen & 0x7F << 24);
                $vlen |= (ord($data{$p++}) << 16);
                $vlen |= (ord($data{$p++}) << 8);
                $vlen |= (ord($data{$p++}));
            }
            $array[substr($data, $p, $nlen)] = substr($data, $p+$nlen, $vlen);
            $p += ($nlen + $vlen);
        }

        return $array;
    }

    /**
     * Decode a FastCGI Packet
     *
     * @param String $data String containing all the packet
     * @return array
     */
    protected function decodePacketHeader($data)
    {
        $ret = array();
        $ret['version']       = ord($data{0});
        $ret['type']          = ord($data{1});
        $ret['requestId']     = (ord($data{2}) << 8) + ord($data{3});
        $ret['contentLength'] = (ord($data{4}) << 8) + ord($data{5});
        $ret['paddingLength'] = ord($data{6});
        $ret['reserved']      = ord($data{7});
        return $ret;
    }

    /**
     * Read a FastCGI Packet
     *
     * @return array
     */
    protected function readPacket()
    {
        $packet = @socket_read($this->sock, self::HEADER_LEN);
        if ($packet === false) {
            throw CommunicationException::socketRead($this->sock);
        }

        $resp = $this->decodePacketHeader($packet);

        if ($len = $resp['contentLength'] + $resp['paddingLength']) {
            $content = @socket_read($this->sock, $len);
            if ($content === false) {
                throw CommunicationException::socketRead($this->sock);
            }
            $resp['content'] = substr($content, 0, $resp['contentLength']);
        } else {
            $resp['content'] = '';
        }

        return $resp;
    }

    /**
     * Get Informations on the FastCGI application
     *
     * @param array $requestedInfo information to retrieve
     * @return array
     */
    public function getValues(array $requestedInfo)
    {
        try {
            return $this->doGetValues($requestedInfo);
        } catch (CommunicationException $e) {
            $this->close();
            throw $e;
        }
    }

    /**
     * Get Informations on the FastCGI application
     *
     * @param array $requestedInfo information to retrieve
     * @return array
     */
    protected function doGetValues(array $requestedInfo)
    {
        $this->connect();

        $request = '';
        foreach ($requestedInfo as $info) {
            $request .= $this->buildNvpair($info, '');
        }

        if (false === @socket_write($this->sock, $this->buildPacket(self::GET_VALUES, $request, 0))) {
            throw CommunicationException::socketWrite($this->sock);
        }

        $this->awaitingResponse = true;
        $resp = $this->readPacket();
        $this->awaitingResponse = false;

        if ($resp['type'] == self::GET_VALUES_RESULT) {
            return $this->readNvpair($resp['content'], $resp['length']);
        } else {
            throw new CommunicationException('Unexpected response type, expecting GET_VALUES_RESULT');
        }
    }

    /**
     * Execute a request to the FastCGI application
     *
     * @param array $params Array of parameters
     * @param String $stdin Content
     */
    public function request(array $params, $stdin)
    {
        try {
            $this->doRequest($params, $stdin);
        } catch (CommunicationException $e) {
            $this->close();
            throw $e;
        }
    }

    /**
     * Execute a request to the FastCGI application
     *
     * @param array $params Array of parameters
     * @param String $stdin Content
     */
    protected function doRequest(array $params, $stdin)
    {
        $this->connect();

        $request = $this->buildPacket(self::BEGIN_REQUEST, chr(0) . chr(self::RESPONDER) . chr((int) $this->keepAlive) . str_repeat(chr(0), 5));

        $paramsRequest = '';
        foreach ($params as $key => $value) {
            $paramsRequest .= $this->buildNvpair($key, $value);
        }
        if ($paramsRequest) {
            $request .= $this->buildPacket(self::PARAMS, $paramsRequest);
        }
        $request .= $this->buildPacket(self::PARAMS, '');

        if ($stdin) {
            $request .= $this->buildPacket(self::STDIN, $stdin);
        }
        $request .= $this->buildPacket(self::STDIN, '');

        // Write the request and break.
        if (false === @socket_write($this->sock, $request)) {
            throw CommunicationException::socketWrite($this->sock);
        }

        $this->awaitingResponse = true;
    }

    /**
     * FCGIClient::formatResponse()
     *
     * Format the response into an array with separate statusCode, headers, body, and error output.
     *
     * @param $stdout The plain, unformatted response.
     * @param $stderr The plain, unformatted error output.
     *
     * @return array An array containing the headers and body content.
     */
    private static function formatResponse($stdout, $stderr)
    {
        // Split the header from the body.  Split on \n\n.
        $doubleCr = strpos($stdout, "\r\n\r\n");
        $rawHeader = substr($stdout, 0, $doubleCr);
        $rawBody = substr($stdout, $doubleCr, strlen($stdout));

        // Format the header.
        $header = array();
        $headerLines = explode("\n", $rawHeader);

        // Initialize the status code and the status header
        $code = '200';
        $headerStatus = '200 OK';

        // Iterate over the headers found in the response.
        foreach ($headerLines as $line) {

            // Extract the header data.
            if (preg_match('/([\w-]+):\s*(.*)$/', $line, $matches)) {

                // Initialize header name/value.
                $headerName = strtolower($matches[1]);
                $headerValue = trim($matches[2]);

                // If we found an status header (will only be available if not have a 200).
                if ($headerName == 'status') {

                    // Initialize the status header and the code.
                    $headerStatus = $headerValue;
                    $code = $headerValue;
                    if (false !== ($pos = strpos($code, ' '))) {
                        $code = substr($code, 0, $pos);
                    }
                }

                // We need to know if this header is already availble
                if (array_key_exists($headerName, $header)) {

                    // Check if the value is an array already
                    if (is_array($header[$headerName])) {
                        // Simply append the next header value
                        $header[$headerName][] = $headerValue;
                    } else {
                        // Convert the existing value into an array and append the new header value
                        $header[$headerName] = array($header[$headerName], $headerValue);
                    }

                } else {
                    $header[$headerName] = $headerValue;
                }
            }
        }

        // Set the status header finally
        $header['status'] = $headerStatus;

        if (false === ctype_digit($code)) {
            throw new CommunicationException("Unrecognizable status code returned from fastcgi: $code");
        }

        return array(
            'statusCode' => (int) $code,
            'headers'    => $header,
            'body'       => trim($rawBody),
            'stderr'     => $stderr,
        );
    }

    /**
     * Collect the response from a FastCGI request.
     *
     * @return String Return response.
     */
    public function response()
    {
        try {
            return $this->doResponse();
        } catch (CommunicationException $e) {
            $this->close();
            throw $e;
        }
    }

    /**
     * Collect the response from a FastCGI request.
     *
     * @return String Return response.
     */
    protected function doResponse()
    {
        $stdout = $stderr = '';

        while ($this->awaitingResponse) {

            $resp = $this->readPacket();

            // Check for the end of the response.
            if ($resp['type'] == self::END_REQUEST || $resp['type'] == 0) {
                $this->awaitingResponse = false;
                // Check for response content.
            } elseif ($resp['type'] == self::STDOUT) {
                $stdout .= $resp['content'];
            } elseif ($resp['type'] == self::STDERR) {
                $stderr .= $resp['content'];
            }
        }

        if (!is_array($resp)) {
            throw new CommunicationException("Bad Request");
        }

        switch (ord($resp['content']{4})) {
            case self::CANT_MPX_CONN:
                throw new CommunicationException('This app can\'t multiplex [CANT_MPX_CONN]');
                break;
            case self::OVERLOADED:
                throw new CommunicationException('New request rejected; too busy [OVERLOADED]');
                break;
            case self::UNKNOWN_ROLE:
                throw new CommunicationException('Role value not known [UNKNOWN_ROLE]');
                break;
            case self::REQUEST_COMPLETE:
                return static::formatResponse($stdout, $stderr);
        }
    }

    public function __toString()
    {
        $type = $this->socketPath ? 'tcp' : 'unix';
        if ($this->awaitingResponse) {
            $status = 'waiting for response';
        } elseif ($this->sock) {
            $status = 'ready for request';
        } else {
            $status = 'not connected';
        }

        $address = $this->socketPath;
        if (!$address) {
            $address = "{$this->host}:{$this->port}";
        }

        return "FCGIClient for $address : $status";
    }
}
