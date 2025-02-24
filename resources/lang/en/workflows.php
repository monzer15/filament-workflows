<?php

return [
    'label' => 'Workflow',
    'plural_label' => 'Workflows',
    'view_logs' => 'View Logs',
    'event_type' => 'Event Type',
    'condition_type' => 'Condition Type',

    'sections' => [
        'description' => [
            'label' => 'Description',
            'description' => 'Provide a detailed description of what this workflow does',
            'placeholder' => 'Enter workflow description...',
        ],
        'grouping' => [
            'label' => 'Group',
            'all' => 'All',
        ],
        'type' => [
            'label' => [
                'workflow_type' => 'Type',
            ],
            'description' => 'Specify what triggers this workflow',
        ],
        'workflow_custom_event' => 'Custom Event Configuration',
        'actions' => [
            'label' => 'Actions',
            'description' => 'Configure the actions that will be executed when this workflow is triggered',
        ],
        'status' => [
            'label' => 'Active',
            'description' => 'Enable or disable this workflow',
        ],
    ],

    'workflow' => [
        'types' => [
            'scheduled' => 'Scheduled',
            'model_event' => 'Record Event',
            'custom_event' => 'Custom Event',
        ],
    ],

    'schedule' => [
        'frequency' => [
            'label' => 'Schedule Frequency',
            'options' => [
                'every_second' => 'Every Second',
                'every_two_seconds' => 'Every 2 Seconds',
                'every_five_seconds' => 'Every 5 Seconds',
                'every_ten_seconds' => 'Every 10 Seconds',
                'every_fifteen_seconds' => 'Every 15 Seconds',
                'every_twenty_seconds' => 'Every 20 Seconds',
                'every_thirty_seconds' => 'Every 30 Seconds',
                'every_minute' => 'Every Minute',
                'every_two_minutes' => 'Every 2 Minutes',
                'every_three_minutes' => 'Every 3 Minutes',
                'every_four_minutes' => 'Every 4 Minutes',
                'every_five_minutes' => 'Every 5 Minutes',
                'every_ten_minutes' => 'Every 10 Minutes',
                'every_fifteen_minutes' => 'Every 15 Minutes',
                'every_thirty_minutes' => 'Every 30 Minutes',
                'hourly' => 'Every Hour',
                'every_two_hours' => 'Every 2 Hours',
                'every_three_hours' => 'Every 3 Hours',
                'every_four_hours' => 'Every 4 Hours',
                'every_six_hours' => 'Every 6 Hours',
                'daily' => 'Daily',
                'daily_at' => 'Daily At',
                'twice_daily' => 'Twice Daily',
                'twice_daily_at' => 'Twice Daily At',
                'weekly' => 'Weekly',
                'weekly_on' => 'Weekly On',
                'monthly' => 'Monthly',
                'monthly_on' => 'Monthly On',
                'twice_monthly' => 'Twice Monthly',
                'last_day_of_month' => 'Last Day of Month',
                'quarterly' => 'Quarterly',
                'quarterly_on' => 'Quarterly On',
                'yearly' => 'Yearly',
                'yearly_on' => 'Yearly On',
            ],
            'helper_text' => 'Enter time in 24-hour format (HH:mm)',
        ],
    ],

    'model' => [
        'events' => [
            'created' => 'New Record Created',
            'updated' => 'Record Updated',
            'deleted' => 'Record Deleted',
        ],
        'attributes' => [
            'label' => 'Model Attribute',
            'any' => 'Any Attribute',
            'updated' => 'Updated Attribute',
        ],
    ],

    'conditions' => [
        'label' => 'Conditions',
        'types' => [
            'none' => 'No condition required',
            'all' => 'All conditions must be true',
            'any' => 'Any condition must be true',
        ],
        'operators' => [
            'equals' => 'Equals',
            'not_equals' => 'Does not equal',
            'greater_equals' => 'Greater than or equals',
            'less_equals' => 'Less than or equals',
            'greater' => 'Greater than',
            'less' => 'Less than',
        ],
    ],

    'custom_event' => [
        'label' => 'Custom Event',
    ],

    'actions' => [
        'magic_attributes' => [
            'label' => 'View magic attributes',
        ],
    ],

    'form' => [
        'run_once' => 'Run Once',
        'active' => 'Active',
        'compare_value' => 'Compare Value',
        'operator' => 'Operator',
    ],

    'table' => [
        'columns' => [
            'description' => 'Description',
            'type' => 'Type',
            'group' => 'Group',
            'actions' => 'Actions',
            'executions_count' => 'Executions Count',
            'last_execution' => 'Last Execution',
            'active' => 'Active',
        ],
        'types' => [
            'scheduled' => 'Scheduled',
            'model_event' => 'Model Event',
            'custom_event' => 'Custom Event',
            'default' => 'Default',
        ],
        'test' => [
            'title' => 'Test Workflow',
            'note' => 'Currently only created event is supported.',
            'fields' => [
                'description' => 'Description',
                'record_event' => 'Record Event',
                'record_id' => 'ID',
                'simulate_attributes' => [
                    'label' => 'Simulate Attribute Changes',
                    'help' => 'This will not update the record',
                ],
                'execute_actions' => 'Execute Actions',
            ],
            'notifications' => [
                'conditions_met' => 'Conditions met!',
                'execution_complete' => 'Execution completed, check logs',
                'conditions_not_met' => 'Conditions did not meet',
            ],
        ],
    ],
];
