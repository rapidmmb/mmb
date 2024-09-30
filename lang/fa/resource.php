<?php

return [

    'default' => [
        'back' => 'بازگشت',
        'page' => 'صفحه :current/:last',
        'none' => 'هیچی',
    ],

    'info' => [
        'message' => 'اطلاعات:',
    ],

    'list' => [
        'message'         => 'صفحه :page از :lastPage:',
        'not_found_label' => '- - آیتمی یافت نشد - -',
    ],

    'create' => [
        'key_label' => 'ایجاد جدید',
    ],

    'edit' => [
        'key_label' => 'ویرایش',
    ],

    'delete' => [
        'key_label' => 'حذف',
        'message'   => 'از حذف اطمینان دارید؟',
        'confirm'   => 'بله، حذف کن!',
    ],

    'soft_delete' => [
        'key_label'     => 'حذف',
        'trash_message' => 'از انتقال به سطل زباله اطمینان دارید؟',
        'trash_confirm' => 'بله، به سطل زباله منتقل کن!',

        'delete_key'     => 'حذف برای همیشه',
        'delete_message' => 'از حذف کردن اطمینان دارید؟ دیگر راهی برای بازیابی نخواهید داشت!',
        'delete_confirm' => 'بله، برای همیشه حذفش کن!',

        'trashed_key_label' => 'در سطل زباله',
        'view_message'      => 'می خواهید بازیابی کنید یا حذف؟',
        'restore_key'       => 'بازیابی',
    ],

    'search' => [
        'message' => 'جستجو:',

        'label'         => 'جستجو',
        'all_key_label' => 'نمایش همه',
    ],

    'order' => [
        'message' => 'ترتیب را انتخاب کنید:',

        'newest' => 'جدیدترین',
        'oldest' => 'قدیمی ترین',

        'key_label' => 'ترتیب: :label',
        'label'     => ':label',
    ],

    'filter' => [
        'message' => 'انتخاب کنید:',
    ],

    'trash' => [
        'key_label' => 'سطل زباله: :label',
        'disabled'  => 'مخفی',
        'enabled'   => 'نمایش',
    ],

];
