<?php
namespace Insead\Events\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Insead\Events\Model\CampusFactory;

class InstallDefaultCampuses implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CampusFactory
     */
    private $campusFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CampusFactory $campusFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CampusFactory $campusFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->campusFactory = $campusFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        
        $campuses = [
            [
                'name' => 'Fontainebleau',
                'code' => 'fontainebleau',
                'description' => 'Our historic campus in the heart of France, surrounded by the beautiful Fontainebleau forest.',
                'sort_order' => 10
            ],
            [
                'name' => 'Singapore',
                'code' => 'singapore',
                'description' => 'Our Asia campus, a hub for business education in the vibrant city-state of Singapore.',
                'sort_order' => 20
            ],
            [
                'name' => 'Abu Dhabi',
                'code' => 'abu-dhabi',
                'description' => 'Our Middle East campus, bringing world-class business education to the UAE.',
                'sort_order' => 30
            ],
            [
                'name' => 'San Francisco',
                'code' => 'san-francisco',
                'description' => 'Our North America hub, connecting INSEAD to the innovation ecosystem of Silicon Valley.',
                'sort_order' => 40
            ],
            [
                'name' => 'Online',
                'code' => 'online',
                'description' => 'Virtual events accessible from anywhere in the world.',
                'sort_order' => 50
            ]
        ];

        foreach ($campuses as $campusData) {
            $campus = $this->campusFactory->create();
            $campus->setData($campusData);
            $campus->save();
        }
        
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}