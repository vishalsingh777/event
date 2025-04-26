<?php
/**
 * INSEAD Events Banner Factory
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Insead\Events\Model\Banner
 */
class BannerFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = Banner::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Insead\Events\Model\Banner
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}