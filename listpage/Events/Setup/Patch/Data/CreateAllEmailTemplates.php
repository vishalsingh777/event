<?php
namespace Insead\Events\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader;

class CreateAllEmailTemplates implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    
    /**
     * @var TemplateFactory
     */
    private $templateFactory;
    
    /**
     * @var State
     */
    private $appState;

    /**
     * @var File
     */
    private $file;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param TemplateFactory $templateFactory
     * @param State $appState
     * @param File $file
     * @param Reader $moduleReader
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        TemplateFactory $templateFactory,
        State $appState,
        File $file,
        Reader $moduleReader
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->templateFactory = $templateFactory;
        $this->appState = $appState;
        $this->file = $file;
        $this->moduleReader = $moduleReader;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        try {
            $currentAreaCode = $this->appState->getAreaCode();
        } catch (\Exception $e) {
            $this->appState->setAreaCode(Area::AREA_FRONTEND);
        }
        
        $this->moduleDataSetup->startSetup();
        
        // Create all email templates
        $templates = [
            [
                'template_code' => 'Insead Events Payment Confirmation',
                'template_subject' => 'Payment Confirmation',
                'orig_template_code' => 'insead_events_payment_template',
                'template_file' => 'payment_confirmation.html'
            ],
            [
                'template_code' => 'Insead Events Free Registration Confirmation',
                'template_subject' => 'Registration Confirmation',
                'orig_template_code' => 'insead_events_free_registration_template',
                'template_file' => 'free_registration_confirmation.html'
            ],
            [
                'template_code' => 'Insead Events Registration Pending',
                'template_subject' => 'Registration Pending Approval',
                'orig_template_code' => 'insead_events_registration_pending',
                'template_file' => 'registration_pending.html'
            ],
            [
                'template_code' => 'Insead Events Registration Approval',
                'template_subject' => 'Registration Approved',
                'orig_template_code' => 'insead_events_registration_approval_template',
                'template_file' => 'registration_approval.html'
            ],
            [
                'template_code' => 'Insead Events Registration Rejection',
                'template_subject' => 'Registration Rejected',
                'orig_template_code' => 'insead_events_registration_rejection_template',
                'template_file' => 'registration_rejection.html'
            ]
        ];
        
        foreach ($templates as $templateData) {
            try {
                // Try to read template content from file
                $filePath = $this->moduleReader->getModuleDir('view', 'Insead_Events') . 
                            '/frontend/email/' . $templateData['template_file'];
                
                $templateText = '';
                if ($this->file->isExists($filePath)) {
                    $templateText = $this->file->fileGetContents($filePath);
                }
                
                // Create the template
                $template = $this->templateFactory->create();
                $template->setTemplateCode($templateData['template_code'])
                    ->setTemplateText($templateText)
                    ->setTemplateType(2) // HTML type
                    ->setTemplateSubject($templateData['template_subject'])
                    ->setOrigTemplateCode($templateData['orig_template_code'])
                    ->setOrigTemplateVariables('{"var customer_name":"Customer Name","var event_title":"Event Title"}');
                
                // Save template
                $template->save();
                
                // Save the template ID to config for debugging
                $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    [
                        'scope' => 'default',
                        'scope_id' => 0,
                        'path' => 'insead_events/debug/' . $templateData['orig_template_code'] . '_id',
                        'value' => $template->getId()
                    ]
                );
                
                // Also update the config to use the numeric template ID
                $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    [
                        'scope' => 'default',
                        'scope_id' => 0,
                        'path' => 'insead_events/email_settings/' . $templateData['orig_template_code'],
                        'value' => $template->getId()
                    ]
                );
                
            } catch (\Exception $e) {
                // Save the error for debugging
                $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    [
                        'scope' => 'default',
                        'scope_id' => 0,
                        'path' => 'insead_events/debug/error_' . $templateData['orig_template_code'],
                        'value' => $e->getMessage()
                    ]
                );
            }
        }
        
        $this->moduleDataSetup->endSetup();
        
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}