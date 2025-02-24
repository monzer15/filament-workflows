<?php

return [
    'label' => 'سير العمل',
    'plural_label' => 'سير العمل',
    'view_logs' => 'عرض السجلات',
    'event_type' => 'نوع الحدث',
    'condition_type' => 'نوع الشرط',

    'sections' => [
        'description' => [
            'label' => 'الوصف',
            'description' => 'قم بتقديم وصف مفصل لما يقوم به سير العمل هذا',
            'placeholder' => 'أدخل وصف سير العمل...',
        ],
        'grouping' => [
            'label' => 'مجموعة سير العمل',
            'all' => 'الكل',
        ],
        'type' => [
            'label' => [
                'workflow_type' => 'نوع سير العمل',
            ],
            'description' => 'حدد ما الذي يُفعّل سير العمل هذا',
        ],
        'workflow_custom_event' => 'إعداد الحدث المخصص',
        'actions' => [
            'label' => 'إجراءات سير العمل',
            'description' => 'قم بتكوين الإجراءات التي سيتم تنفيذها عند تفعيل سير العمل هذا',
        ],
        'status' => [
            'label' => 'نشط',
            'description' => 'تفعيل أو تعطيل سير العمل هذا',
        ],
    ],

    'workflow' => [
        'types' => [
            'scheduled' => 'مجدول',
            'model_event' => 'حدث النموذج',
            'custom_event' => 'حدث مخصص',
        ],
    ],

    'schedule' => [
        'frequency' => [
            'label' => 'تكرار الجدولة',
            'options' => [
                'every_second' => 'كل ثانية',
                'every_two_seconds' => 'كل ثانيتين',
                'every_five_seconds' => 'كل 5 ثواني',
                'every_ten_seconds' => 'كل 10 ثواني',
                'every_fifteen_seconds' => 'كل 15 ثانية',
                'every_twenty_seconds' => 'كل 20 ثانية',
                'every_thirty_seconds' => 'كل 30 ثانية',
                'every_minute' => 'كل دقيقة',
                'every_two_minutes' => 'كل دقيقتين',
                'every_three_minutes' => 'كل 3 دقائق',
                'every_four_minutes' => 'كل 4 دقائق',
                'every_five_minutes' => 'كل 5 دقائق',
                'every_ten_minutes' => 'كل 10 دقائق',
                'every_fifteen_minutes' => 'كل 15 دقيقة',
                'every_thirty_minutes' => 'كل 30 دقيقة',
                'hourly' => 'كل ساعة',
                'every_two_hours' => 'كل ساعتين',
                'every_three_hours' => 'كل 3 ساعات',
                'every_four_hours' => 'كل 4 ساعات',
                'every_six_hours' => 'كل 6 ساعات',
                'daily' => 'يومياً',
                'daily_at' => 'يومياً في',
                'twice_daily' => 'مرتين يومياً',
                'twice_daily_at' => 'مرتين يومياً في',
                'weekly' => 'أسبوعياً',
                'weekly_on' => 'أسبوعياً في',
                'monthly' => 'شهرياً',
                'monthly_on' => 'شهرياً في',
                'twice_monthly' => 'مرتين شهرياً',
                'last_day_of_month' => 'آخر يوم من الشهر',
                'quarterly' => 'ربع سنوي',
                'quarterly_on' => 'ربع سنوي في',
                'yearly' => 'سنوياً',
                'yearly_on' => 'سنوياً في',
            ],
            'helper_text' => 'أدخل الوقت بتنسيق 24 ساعة (HH:mm)',
        ],
    ],

    'model' => [
        'events' => [
            'created' => 'تم إنشاء سجل جديد',
            'updated' => 'تم تحديث السجل',
            'deleted' => 'تم حذف السجل',
        ],
        'attributes' => [
            'label' => 'سمة النموذج',
            'any' => 'أي سمة',
            'updated' => 'السمة المحدثة',
        ],
    ],

    'conditions' => [
        'label' => 'الشروط',
        'types' => [
            'none' => 'لا يلزم شرط',
            'all' => 'يجب أن تكون جميع الشروط صحيحة',
            'any' => 'يجب أن يكون أي شرط صحيحاً',
        ],
        'operators' => [
            'equals' => 'يساوي',
            'not_equals' => 'لا يساوي',
            'greater_equals' => 'أكبر من أو يساوي',
            'less_equals' => 'أقل من أو يساوي',
            'greater' => 'أكبر من',
            'less' => 'أقل من',
        ],
    ],

    'custom_event' => [
        'label' => 'حدث مخصص',
    ],

    'actions' => [
        'magic_attributes' => [
            'label' => 'عرض السمات السحرية',
        ],
    ],

    'form' => [
        'run_once' => 'تشغيل مرة واحدة',
        'active' => 'نشط',
        'compare_value' => 'قيمة المقارنة',
        'operator' => 'العامل',
    ],

    'table' => [
        'columns' => [
            'description' => 'الوصف',
            'type' => 'النوع',
            'group' => 'المجموعة',
            'actions' => 'الإجراءات',
            'executions_count' => 'عدد مرات التنفيذ',
            'last_execution' => 'آخر تنفيذ',
            'active' => 'نشط',
        ],
        'types' => [
            'scheduled' => 'مجدول',
            'model_event' => 'حدث النموذج',
            'custom_event' => 'حدث مخصص',
            'default' => 'افتراضي',
        ],
        'test' => [
            'title' => 'اختبار سير العمل',
            'note' => 'حالياً يتم دعم حدث الإنشاء فقط',
            'fields' => [
                'description' => 'الوصف',
                'record_event' => 'حدث السجل',
                'record_id' => 'المعرف',
                'simulate_attributes' => [
                    'label' => 'محاكاة تغييرات السمات',
                    'help' => 'هذا لن يقوم بتحديث السجل',
                ],
                'execute_actions' => 'تنفيذ الإجراءات',
            ],
            'notifications' => [
                'conditions_met' => 'تم استيفاء الشروط!',
                'execution_complete' => 'اكتمل التنفيذ، راجع السجلات',
                'conditions_not_met' => 'لم يتم استيفاء الشروط',
            ],
        ],
    ],
];
