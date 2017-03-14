<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */
namespace PHPRouter;

use Fig\Http\Message\RequestMethodInterface;

class Route
{

    /**
     * URL of this Route
     *
     * @var string
     */
    private $url;

    /**
     * Accepted HTTP methods for this route.
     *
     * @var string[]
     */
    private $methods = array(
        RequestMethodInterface::METHOD_GET,
        RequestMethodInterface::METHOD_POST,
        RequestMethodInterface::METHOD_PUT,
        RequestMethodInterface::METHOD_DELETE,
    );

    /**
     * Target for this route, can be anything.
     *
     * @var mixed
     */
    private $target;

    /**
     * The name of this route, used for reversed routing
     *
     * @var string
     */
    private $name;

    /**
     * Custom parameter filters for this route
     *
     * @var array
     */
    private $filters = array();

    /**
     * Array containing parameters passed through request URL
     *
     * @var array
     */
    private $parameters = array();

    /**
     * Set named parameters to target method
     *
     * @example [ [0] => [ ["link_id"] => "12312" ] ]
     * @var bool
     */
    private $parametersByName;

    /**
     *
     *
     * @var array
     */
    private $config;

    /**
     *
     *
     * @param unknown $resource
     * @param array   $config
     */
    public function __construct($resource, array $config)
    {
        $this->url     = $resource;
        $this->config  = $config;
        $this->methods = isset($config['methods']) ? (array) $config['methods'] : array();
        $this->target  = isset($config['target']) ? $config['target'] : null;
        $this->name    = isset($config['name']) ? $config['name'] : null;
        if (isset($config['filters'])) {
            $this->setFilters($config['filters'],  true);
        }
    }


    /**
     *
     *
     * @return
     */
    public function getUrl()
    {
        return $this->url;
    }


    /**
     *
     *
     * @param string  $url
     */
    public function setUrl($url)
    {
        $url = (string)$url;

        // make sure that the URL is suffixed with a forward slash
        if (substr($url, - 1) !== '/') {
            $url .= '/';
        }

        $this->url = $url;
    }


    /**
     *
     *
     * @return
     */
    public function getTarget()
    {
        return $this->target;
    }


    /**
     *
     *
     * @param string  $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }


    /**
     *
     *
     * @return
     */
    public function getMethods()
    {
        return $this->methods;
    }


    /**
     *
     *
     * @param array   $methods
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;
    }


    /**
     *
     *
     * @return
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     *
     *
     * @param string  $name
     */
    public function setName($name)
    {
        $this->name = (string)$name;
    }


    /**
     *
     *
     * @param array   $filters
     * @param string  $parametersByName (optional)
     */
    public function setFilters(array $filters, $parametersByName = false)
    {
        $this->filters          = $filters;
        $this->parametersByName = $parametersByName;
    }


    /**
     *
     *
     * @return
     */
    public function getRegex()
    {
        return preg_replace_callback('/(:\w+)/', array( & $this, 'substituteFilter'), $this->url);
    }


    /**
     *
     *
     * @param string  $matches
     * @return
     */
    private function substituteFilter($matches)
    {
        if (isset($matches[1], $this->filters[$matches[1]])) {
            return $this->filters[$matches[1]];
        }

        return '([\w-\.%]+)';
    }


    /**
     *
     *
     * @return
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     *
     *
     * @param array   $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }


    /**
     *
     *
     * @return
     */
    public function dispatch()
    {
        $action = explode('::', $this->config['_controller']);

        if ($this->parametersByName) {
            $this->parameters = array($this->parameters);
        }

        $this->action = !empty($action[1]) && trim($action[1]) !== '' ? $action[1] : null;

        if (!is_null($this->action)) {
            // TODO: remove once autoloading
            require_once CONTROLLERS_PATH . '/' . $action[0] . '.php';

            $controller = new $action[0];
            call_user_func_array(array($controller, $this->action), $this->parameters);
        } else {
            $controller = new $action[0]($this->parameters);
        }
    }


    /**
     *
     *
     * @return
     */
    public function getAction()
    {
        return $this->action;
    }
}
