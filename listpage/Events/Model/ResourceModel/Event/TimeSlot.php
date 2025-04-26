<?php
namespace Insead\Events\Model\ResourceModel\Event;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TimeSlot extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('insead_event_times', 'id');
    }
    
    /**
     * Get time slots by event ID
     *
     * @param int $eventId
     * @return array
     */
    public function getTimeSlotsByEventId($eventId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('event_id = ?', $eventId)
            ->order('sort_order ASC');
            
        return $connection->fetchAll($select);
    }
    
    /**
     * Delete time slots by event ID
     *
     * @param int $eventId
     * @return int Number of deleted rows
     */
    public function deleteByEventId($eventId)
    {
        $connection = $this->getConnection();
        return $connection->delete(
            $this->getMainTable(),
            ['event_id = ?' => $eventId]
        );
    }
    
    /**
     * Save multiple time slots for an event
     *
     * @param int $eventId
     * @param array $timeSlots
     * @return $this
     */
    public function saveTimeSlots($eventId, array $timeSlots)
    {
        if (empty($timeSlots)) {
            return $this;
        }
        
        $connection = $this->getConnection();
        $data = [];
        $sortOrder = 0;
        
        foreach ($timeSlots as $rowId => $rowData) {
            // The form structure nests the actual fields under 'date_time' key
            $fields = isset($rowData['date_time']) ? $rowData['date_time'] : $rowData;
            
            if (!isset($fields['time_start']) || !isset($fields['time_end'])) {
                continue;
            }
            
            // Check if row is marked for deletion
            if (isset($rowData['is_deleted']) && $rowData['is_deleted'] == 1) {
                continue;
            }
            
            $data[] = [
                'event_id' => $eventId,
                'time_start' => $fields['time_start'],
                'time_end' => $fields['time_end'],
                'sort_order' => $sortOrder++,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        
        if (!empty($data)) {
            $connection->insertMultiple($this->getMainTable(), $data);
        }
        
        return $this;
    }
}