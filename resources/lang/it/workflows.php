<?php

return [
    'label' => 'Workflow',
    'plural_label' => 'Workflows',
    'view_logs' => 'Vedi registri',
    'event_type' => 'Tipo di evento',
    'condition_type' => 'Tipo di condizione',

    'sections' => [
        'description' => [
            'label' => 'Descrizione',
            'description' => 'Fornisci una descrizione dettagliata dello scopo di questo workflow',
            'placeholder' => 'Inserisci una descrizione del workflow...',
        ],
        'grouping' => [
            'label' => 'Gruppo',
            'all' => 'Tutti',
        ],
        'type' => [
            'label' => [
                'workflow_type' => 'Tipo',
            ],
            'description' => 'Specifica cosa attiva questo workflow',
        ],
        'workflow_custom_event' => 'Configurazione evento personalizzato',
        'actions' => [
            'label' => 'Azioni',
            'description' => "Configura l'azione che verrà eseguita quando questo workflow viene attivato",
        ],
        'status' => [
            'label' => 'Attivo',
            'description' => 'Attiva o disattiva questo workflow',
        ],
    ],

    'workflow' => [
        'types' => [
            'scheduled' => 'Pianificato',
            'model_event' => 'Evento di un record',
            'custom_event' => 'Evento personalizzato',
        ],
    ],

    'schedule' => [
        'frequency' => [
            'label' => 'Frequenza di pianificazione',
            'options' => [
                'every_second' => 'Ogni secondo',
                'every_two_seconds' => 'Ogni 2 secondi',
                'every_five_seconds' => 'Ogni 5 secondi',
                'every_ten_seconds' => 'Ogni 10 secondi',
                'every_fifteen_seconds' => 'Ogni 15 secondi',
                'every_twenty_seconds' => 'Ogni 20 secondi',
                'every_thirty_seconds' => 'Ogni 30 secondi',
                'every_minute' => 'Ogni minuto',
                'every_two_minutes' => 'Ogni 2 minuti',
                'every_three_minutes' => 'Ogni 3 minuti',
                'every_four_minutes' => 'Ogni 4 minuti',
                'every_five_minutes' => 'Ogni 5 minuti',
                'every_ten_minutes' => 'Ogni 10 minuti',
                'every_fifteen_minutes' => 'Ogni 15 minuti',
                'every_thirty_minutes' => 'Ogni 30 minuti',
                'hourly' => 'Ogni ora',
                'every_two_hours' => 'Ogni 2 ore',
                'every_three_hours' => 'Ogni 3 ore',
                'every_four_hours' => 'Ogni 4 ore',
                'every_six_hours' => 'Ogni 6 ore',
                'daily' => 'Ogni giorno',
                'daily_at' => 'Ogni giorno alle',
                'twice_daily' => 'Due volte al giorno',
                'twice_daily_at' => 'Due volte al giorno alle',
                'weekly' => 'Settimanalmente',
                'weekly_on' => 'Settimanalmente alle',
                'monthly' => 'Mensilmente',
                'monthly_on' => 'Mensilmente alle',
                'twice_monthly' => 'Due volte al mese',
                'last_day_of_month' => "L'ultimo giorno del mese",
                'quarterly' => 'Ogni trimestre',
                'quarterly_on' => 'Ogni trimestre alle',
                'yearly' => 'Annualmente',
                'yearly_on' => 'Annualmente alle',
            ],
            'helper_text' => 'Inserisci il tempo nel formato 24 ore (HH:mm)',
        ],
    ],

    'model' => [
        'events' => [
            'created' => 'Nuovo record creato',
            'updated' => 'Record aggiornato',
            'deleted' => 'Record cancellato',
        ],
        'attributes' => [
            'label' => 'Attributo del record',
            'any' => 'Qualsiasi attributo',
            'updated' => 'Attributo aggiornato',
        ],
    ],

    'conditions' => [
        'label' => 'Condizioni',
        'types' => [
            'none' => 'Nessuna condizione richiesta',
            'all' => 'Tutte le condizioni devono essere vere',
            'any' => 'Una qualsiasi condizione deve essere vera',
        ],
        'operators' => [
            'equals' => 'Uguale a',
            'not_equals' => 'Non uguale a',
            'greater_equals' => 'Maggiore o uguale a',
            'less_equals' => 'Minore o uguale a',
            'greater' => 'Maggiore di',
            'less' => 'Minore di',
        ],
    ],

    'custom_event' => [
        'label' => 'Evento personalizzato',
    ],

    'actions' => [
        'magic_attributes' => [
            'label' => 'Vedi gli attributi magici',
        ],
    ],

    'form' => [
        'run_once' => 'Esegui una sola volta',
        'active' => 'Attiva',
        'compare_value' => 'Confronta valore',
        'operator' => 'Operatore',
    ],

    'table' => [
        'columns' => [
            'description' => 'Descrizione',
            'type' => 'Tipo',
            'group' => 'Gruppo',
            'actions' => 'Azioni',
            'executions_count' => 'Numero di esecuzioni',
            'last_execution' => 'Ultima esecuzione',
            'active' => 'Attivo',
        ],
        'types' => [
            'scheduled' => 'Pianificato',
            'model_event' => 'Evento di un record',
            'custom_event' => 'Evento personalizzato',
            'default' => 'Default',
        ],
        'test' => [
            'title' => 'Workflow di test',
            'note' => 'Al momento solo la creazione evento è supportata.',
            'fields' => [
                'description' => 'Descrizione',
                'record_event' => 'Evento del record',
                'record_id' => 'ID',
                'simulate_attributes' => [
                    'label' => 'Simula un cambiamento di attributi',
                    'help' => 'Il record non verrà aggiornato',
                ],
                'execute_actions' => 'Esegui azioni',
            ],
            'notifications' => [
                'conditions_met' => 'Condizioni soddisfatte!',
                'execution_complete' => 'Esecuzione completata, controlla il registro',
                'conditions_not_met' => 'Le condizioni non sono state soddisfatte',
            ],
        ],
    ],
];
