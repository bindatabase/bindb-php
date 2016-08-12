<?php namespace Riad;

/**
 * A class for doing bin lookups.
 * This file is part of the Bindb PHP package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @property string|null $app_token Your secret token.
 * @property bool $errorMode Either throw an exception or not.
 * @property array $fields Fields to get.
 * @property string $raw_response raw json response.
 * @property int|string $bindbql_bin Bin got from BinDBQL.
 * @property bool $using_bql whether BinDBQL query is built or not.
 *
 * @author Riad Loukili <riad.loukili@outlook.com>
 * @copyright Riad Loukili (https://bindb.me) <riad.loukili@outlook.com>
 * @license MIT
 */

class Bin
{

    private $app_token = null;
    private $errorMode = false;
    private $fields = [];
    private $raw_response;
    private $bindbql_bin;
    private $using_bql = false;

    /**
     * The class constructor.
     * @param string|null $app_token Your secret token
     */
    public function __construct($app_token = null)
    {
        if (!is_string($app_token) && !is_null($app_token))
            throw new \InvalidArgumentException('App Token must be string, ' . gettype($app_token) . ' given.');

        $this->app_token = $app_token;
        return $this;
    }

    /**
     * Get a bin information (raw).
     * @param int|string $bin The 6 digits bin number
     * @return string Raw JSON of the response containing the information
     */
    public function raw($bin)
    {
        $fields = $this->fields;
        $fields_query = '';
        if (!empty($fields)) {
            $fields_query = '?fields=';
            foreach ($fields as $field)
                $fields_query .= $field . ',';
            $fields_query = rtrim($fields_query, ',');
        }
        $resource = is_null($this->app_token) ? 'public' : "private/{$this->app_token}";
        $url = "https://bindb.me/api/{$resource}/json/{$bin}{$fields_query}";
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true
        ]);
        $response = curl_exec($ch);
        $this->raw_response = $response;
        return $response;
    }

    /**
     * Get a bin information.
     * @param int|string $bin The 6 digits bin number
     * @param bool $array Whether return an array to an object
     * @return null|object|array The response containing the information
     * @throws \Exception
     */
    public function get($bin, $array = false)
    {
        $response = $this->raw($bin);
        $object = json_decode($response);
        if (isset($object->error) && true === $object->error) {
            if ($this->errorMode)
                throw new \Exception($object->errorDetails->message, $object->errorDetails->code);
            return null;
        }

        if ($array)
            return (array) $object;

        return $object;
    }

    /**
     * Get a bin information.
     * @alias get()
     * @param int|string $bin The 6 digits bin number
     * @param bool $array
     * @return null|object|array The response containing the information
     * @throws \Exception
     */
    public function search($bin, $array = false)
    {
        return $this->get($bin, $array);
    }

    /**
     * Get a bin information.
     * @alias get()
     * @param int|string $bin The 6 digits bin number
     * @param bool $array
     * @return null|object|array The response containing the information
     * @throws \Exception
     */
    public function lookup($bin, $array = false)
    {
        return $this->get($bin, $array);
    }

    /**
     * Switch error mode.
     * @param bool $error_mode
     * @return $this
     */
    public function error($error_mode = false)
    {
        $this->errorMode = $error_mode;
        return $this;
    }

    /**
     * Select fields
     * @param array $fields
     * @return $this
     */
    public function fields(array $fields = [])
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Get bins using SQL-like syntax
     * @param string $query
     * @return $this
     */
    public function query($query)
    {
        $this->using_bql = true;
        preg_match_all("/SELECT[\n|\r|\s|\t]{0,}(.*?)[\n|\r|\s|\t]{0,}FROM[\n|\r|\s|\t]{0,}bins[\n|\r|\s]{0,}WHERE[\n|\r|\s|\t]{0,}bin[\n|\r|\s|\t]{0,}=[\n|\r|\s|\t]{0,}(.*)/i", $query, $matches);
        $fields = $matches[1][0];
        $bin = $matches[2][0];
        $this->bindbql_bin = $bin;
        if ($fields !== '*') {
            $this->fields = explode(',', str_replace(' ', '', $fields));
        }
        return $this;
    }

    /**
     * Run BinDBQL query
     * @param array|string|int $params
     * @return null|object
     * @throws \RuntimeException|\LogicException
     */
    public function run($params = [])
    {
        // if the query is built
        if ($this->using_bql) {
            // If the bin hasn't been set yet
            $_bin = $this->bindbql_bin;
            if ($this->bindbql_bin == '?') {
                // If the param is array.
                if (isset($params) && !empty($params) && (is_array($params) || is_int($params) || is_string($params))) {
                    if (is_array($params)) {
                        $_bin = urlencode($params[0]);
                    } else {
                        $_bin = urlencode($params);
                    }
                } else {
                    if ($this->errorMode)
                        throw new \RuntimeException('The query was waiting for one argument, received none');
                    return null;
                }
            }
            return $this->get($_bin);
        } else {
            if ($this->errorMode)
                throw new \LogicException('You\'re trying to run a query you have\'nt built yet');
            return null;
        }
    }

}
