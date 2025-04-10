<?php
namespace Vishal\Events\Ui\Component\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class TimeSlots implements ModifierInterface
{
    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        foreach ($data as $eventId => $eventData) {
            if (isset($eventData['time_slots'])) {
                $timeSlots = $eventData['time_slots'];
                
                // If it's a JSON string, decode it
                if (is_string($timeSlots) && !empty($timeSlots)) {
                    try {
                        $timeSlotsArray = json_decode($timeSlots, true);
                        if (is_array($timeSlotsArray)) {
                            $data[$eventId]['time_slots_grid'] = $timeSlotsArray;
                        }
                    } catch (\Exception $e) {
                        // If not JSON, try comma separation
                        $timeSlotsArray = explode(',', $timeSlots);
                        $data[$eventId]['time_slots_grid'] = $timeSlotsArray;
                    }
                }
            }
            
            // Handle blocked dates
            if (isset($eventData['block_dates'])) {
                $blockedDates = $eventData['block_dates'];
                
                // If it's a JSON string, decode it
                if (is_string($blockedDates) && !empty($blockedDates)) {
                    try {
                        $blockedDatesArray = json_decode($blockedDates, true);
                        if (is_array($blockedDatesArray)) {
                            $data[$eventId]['blocked_dates_list'] = $blockedDatesArray;
                        }
                    } catch (\Exception $e) {
                        // Handle as text if not JSON
                        $blockedDatesArray = explode("\n", $blockedDates);
                        $blockedDatesArray = array_map('trim', $blockedDatesArray);
                        $blockedDatesArray = array_filter($blockedDatesArray);
                        $data[$eventId]['blocked_dates_list'] = $blockedDatesArray;
                    }
                }
            }
        }
        
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        if (isset($meta['time_slots_fieldset']['children']['time_slots_selection']['children']['time_slots_grid'])) {
            $meta['time_slots_fieldset']['children']['time_slots_selection']['children']['time_slots_grid']['arguments']['data']['config']['additionalClasses'] = 'time-slots-checkboxset';
            $meta['time_slots_fieldset']['children']['time_slots_selection']['children']['time_slots_grid']['arguments']['data']['config']['template'] = 'Vishal_Events/form/field/time-slots';
        }
        
        return $meta;
    }
}