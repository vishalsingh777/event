<?php
namespace Insead\Events\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Insead\Events\Model\CategoryFactory;

class InstallDefaultCategories implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategoryFactory $categoryFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        
        $categories = [
            [
                'name' => 'Alumni Events',
                'code' => 'alumni',
                'icon_class' => 'people',
                'sort_order' => 10
            ],
            [
                'name' => 'digital@INSEAD',
                'code' => 'digital',
                'icon_class' => 'computer',
                'sort_order' => 20
            ],
            [
                'name' => 'Centres & Initiatives',
                'code' => 'centers',
                'icon_class' => 'business',
                'sort_order' => 30
            ],
            [
                'name' => 'Executive Education',
                'code' => 'executive',
                'icon_class' => 'school',
                'sort_order' => 40
            ],
            [
                'name' => 'Master Programmes',
                'code' => 'masters',
                'icon_class' => 'workspace_premium',
                'sort_order' => 50
            ],
            [
                'name' => 'Conferences',
                'code' => 'conference',
                'icon_class' => 'groups',
                'sort_order' => 60
            ]
        ];

        foreach ($categories as $categoryData) {
            $category = $this->categoryFactory->create();
            $category->setData($categoryData);
            $category->save();
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