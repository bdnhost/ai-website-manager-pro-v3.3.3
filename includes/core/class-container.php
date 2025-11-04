<?php
/**
 * Dependency Injection Container
 *
 * @package AI_Manager_Pro
 * @subpackage Core
 */

namespace AI_Manager_Pro\Core;

/**
 * Simple Dependency Injection Container
 */
class Container
{

    /**
     * Container instance
     *
     * @var Container
     */
    private static $instance = null;

    /**
     * Services registry
     *
     * @var array
     */
    private $services = [];

    /**
     * Singletons registry
     *
     * @var array
     */
    private $singletons = [];

    /**
     * Get container instance
     *
     * @return Container
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
    }

    /**
     * Register a service
     *
     * @param string $name Service name
     * @param callable $factory Factory function
     * @param bool $singleton Whether to treat as singleton
     */
    public function register($name, $factory, $singleton = false)
    {
        $this->services[$name] = [
            'factory' => $factory,
            'singleton' => $singleton
        ];
    }

    /**
     * Get a service
     *
     * @param string $name Service name
     * @return mixed Service instance or null if not found
     */
    public function get($name)
    {
        if (!isset($this->services[$name])) {
            error_log("AI Manager Pro: Service '{$name}' not found in container");
            return null;
        }

        $service = $this->services[$name];

        // Return singleton if already instantiated
        if ($service['singleton'] && isset($this->singletons[$name])) {
            return $this->singletons[$name];
        }

        try {
            // Create new instance
            $instance = call_user_func($service['factory'], $this);

            // Store singleton
            if ($service['singleton']) {
                $this->singletons[$name] = $instance;
            }

            return $instance;
        } catch (\Exception $e) {
            error_log("AI Manager Pro: Failed to create service '{$name}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if service exists
     *
     * @param string $name Service name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->services[$name]);
    }

    /**
     * Remove a service
     *
     * @param string $name Service name
     */
    public function remove($name)
    {
        unset($this->services[$name], $this->singletons[$name]);
    }

    /**
     * Get all registered services
     *
     * @return array
     */
    public function get_services()
    {
        return array_keys($this->services);
    }
}

