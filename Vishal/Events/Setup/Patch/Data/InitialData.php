<?php
/**
 * InitialData.php
 * Path: app/code/Vishal/Events/Setup/Patch/Data/InitialData.php
 */

declare(strict_types=1);

namespace Vishal\Events\Setup\Patch\Data;

use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\EventRepository;
use Vishal\Events\Model\EventTicketFactory;
use Vishal\Events\Model\EventTicketRepository;

class InitialData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var EventFactory
     */
    private $eventFactory;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EventTicketFactory
     */
    private $eventTicketFactory;

    /**
     * @var EventTicketRepository
     */
    private $eventTicketRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param State $appState
     * @param EventFactory $eventFactory
     * @param EventRepository $eventRepository
     * @param EventTicketFactory $eventTicketFactory
     * @param EventTicketRepository $eventTicketRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        State $appState,
        EventFactory $eventFactory,
        EventRepository $eventRepository,
        EventTicketFactory $eventTicketFactory,
        EventTicketRepository $eventTicketRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->appState = $appState;
        $this->eventFactory = $eventFactory;
        $this->eventRepository = $eventRepository;
        $this->eventTicketFactory = $eventTicketFactory;
        $this->eventTicketRepository = $eventTicketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        try {
            // Sometimes need to set area code in setup patch
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            // Area code already set
        }

        $this->moduleDataSetup->startSetup();

        // Create sample event
        $sampleEvent = $this->eventFactory->create();
        $sampleEvent->setData([
            'event_title' => 'Sample Event',
            'event_venue' => 'Sample Venue',
            'url_key' => 'sample-event',
            'color' => '#1979c3',
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+1 week +2 hours')),
            'content' => '<p>This is a sample event created during installation. You can edit or delete it from the admin panel.</p>',
            'youtube_video_url' => '',
            'status' => 1,
            'recurring' => 0,
            'contact_person' => 'John Doe',
            'phone' => '123-456-7890',
            'email' => 'contact@example.com',
            'address' => '123 Main St, Anytown, USA',
            'page_title' => 'Sample Event',
            'meta_keywords' => 'sample, event, magento',
            'meta_description' => 'This is a sample event created during Vishal_Events module installation.',
            'store_id' => [Store::DEFAULT_STORE_ID]
        ]);

        // Save the event
        $sampleEvent = $this->eventRepository->save($sampleEvent);

        // Create sample tickets
        $tickets = [
            [
                'name' => 'General Admission',
                'sku' => 'event-ticket-general',
                'price' => 19.99,
                'position' => 1
            ],
            [
                'name' => 'VIP Access',
                'sku' => 'event-ticket-vip',
                'price' => 59.99,
                'position' => 2
            ]
        ];

        foreach ($tickets as $ticketData) {
            $ticket = $this->eventTicketFactory->create();
            $ticket->setData($ticketData);
            $ticket->setEventId($sampleEvent->getId());
            $this->eventTicketRepository->save($ticket);
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