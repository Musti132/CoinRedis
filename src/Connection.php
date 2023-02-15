<?php

namespace CoinRedis;

use CoinRedis\Exceptions\ConnectionError;
use Socket\Raw\Factory;
use Socket\Raw\Socket;
use Socket\Raw\Exception as SocketException;
use DateTime;

class Connection
{
    public string $host;
    public int $bytes = 8192;

    private Socket $connector;
    private Factory $factory;

    /**
     * Opens a connection to the server.
     * 
     * @param string $ip
     * @param int $port
     * @param array|null $options
     */
    public function __construct(string $ip, int $port, array $options = null)
    {
        $this->host = $ip . ":" . $port;

        $this->factory = new Factory();

        try {
            $this->connector = $this->factory->createClient($this->host);
        } catch (SocketException $e) {
            throw new ConnectionError($e->getMessage());
        }
    }

    /**
     * Write to server
     * 
     * @param string $data Data to write
     * 
     * @return string
     */
    public function write(string $data)
    {
        $this->connector->write($data . "\r\n");

        $data = $this->read($this->connector);

        return $data;
    }

    /**
     * Read response from server
     * 
     * @param Socket $socket
     * 
     * @return string
     */
    public function read(Socket $socket)
    {
        $data = $socket->read($this->bytes);

        $data = str_replace('+OK', '', $data);

        $prefix = $data[0];

        $payload = trim($data);

        /**
         * Determine Data Type 
         * 
         * RESP Protocol description (https://redis.io/docs/reference/protocol-spec/#resp-protocol-description)
         */
        switch ($prefix) {

            // Simple String
            case '+':
                return substr($payload, 2);
            
            // Bulk Strings
            case '$':
                $size = (int) $payload;

                if ($size === -1) {
                    return;
                }

                if($payload == "$-1") {
                    return null;
                }


                return trim(substr($data, 2, -2));

            // Arrays
            case '*':
                $count = (int) $payload;

                if ($count === -1) {
                    return;
                }
                return $count;
            
            // Integers
            case ':':
                //$integer = (int) $payload;
                $integer = str_replace(':', '', $payload);
                //return $integer == $payload ? $integer : $payload;

                return $integer;
            
            // Errors
            case '-':
                return ($payload);

            default:
                return "nothing";
        }

        //$formatted = $this->formatData($data);

        //return $formatted;
    }

    /**
     * Returns the client.
     * 
     * @return Socket\Raw\Socket;
     */
    public function connection()
    {
        return $this->connector;
    }

    /**
     * Closes the connection to the server.
     * 
     * @return Socket\Raw\Socket
     */
    public function close()
    {
        return $this->connector->close();
    }
}
