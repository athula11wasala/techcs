<?php
return [

    'from_email' => 'technical@newfrontierdata.com',

    'system_email_send' => 'EQUIO',

    'company_profile_cover' => 'storage/profiles/company/cover/',

    'company_profile_document' => 'storage/profiles/company/full_pdf/',

    'company_profile_logo' => 'storage/profiles/company/logo/',

    'chart-image' => 'storage/chart/chart-image/',

    'enterprise-pdf' => 'storage/chart/enterprise-pdf/',

    'alert_image' => 'storage/alert-image/',

    'WOOCOMMERCE_API_KEY' => env('WOOCOMMERCE_API_KEY'),

    'WOOCOMMERCE_API_SECRET' => env('WOOCOMMERCE_API_SECRET'),

    'WOOCOMMERCE_API_URL' => env('WOOCOMMERCE_API_URL'),

    'WOOCOMMERCE_CATEGORY_ID' => env('WOOCOMMERCE_CATEGORY_ID'),

    'STRIPE_API_KEY' => env('STRIPE_API_KEY'),


    'STRIPE_API_VERSION' => '2018-11-08',

    'STRIPE_PRODUCT_KEY' => env('STRIPE_PRODUCT_KEY'),


    //'WOOCOMMERCE_API_KEY_SUBSCRIPTION' => 'ck_498bdf69061844f08551327bf189d784d8c8c528',

    //'WOOCOMMERCE_API_SECRET_SUBSCRIPTION' => 'cs_ee4f00c96964a8fe2ba6831e2f71fd47d684e2db',

    'REPORTS_STORAGE' => '/storage/reports/',

    'REPORTS_COVER' => 'cover/',

    'REPORTS_CHART_KEYWORD' => 'chart_keyword/',

    'CONSUMER_ICON_STORAGE' => '/storage/consumer_icon/',

    'REPORTS_CHART_FILENAME' => 'charts/',

    'REPORTS_FULL_PDF' => 'full_pdf/',

    'REPORTS_ENTERPRISES_PDF' => 'enterprise_pdf/',

    'REPORTS_SUMMERY_PDF' => 'summary_pdf/',

    'WEBINAR_FULL_PDF' => '/storage/webinars/full_pdf/',

    'INVESTMENT_RANK_STORAGE' => '/storage/investment_rank/',

    'insight_daily_image_url' => '/storage/in-sight-daily/',

    'PACKAGE_ENTERPRISE' => "enterprise",

    'PACKAGE_PREMIUMPLUS' => "premium_plus",

    'PACKAGE_PREMIUM' => "premium",

    'PACKAGE_ESSENTIAL' => "essential",

    'CHART_SEARCH_BY_KEYWORD' => 1,

    'CHART_SEARCH_BY_NAME' => 2,

    'data_set' => ['LegalizedStates' => '1', 'QualifyingConditions' => '2', 'TaxRates' => '3',
        'CannabisBenchmarksUs' => '4', 'cannibalization' => '5', 'investment_ranking_threshold_us' => '7'],

    'data_set_legal_state' => 'storage/dataset/1/',

    'data_set_qualify_condition' => 'storage/dataset/2/',

    'data_set_tax_rate' => 'storage/dataset/3/',

    'data_set_caninbench_markus' => 'storage/dataset/4/',

    'company_profile_type' => ['1' => 'company', '2' => 'country'],

    'company_profile_status' => ['1' => 'active', '2' => 'deactivate', '3' => 'pending'],

    'ROLE_ADMINISTRATOR' => "1",

    'ROLE_EQUIO' => "2",

    'ROLE_MANAGER' => "3",

    'ROLE_EDITOR' => "4",

    'ROLE_REPORTER' => "5",

    'CHART_PAGINATE_PER_PAGE' => 12,

    'INTERACTIVE_PAGINATE_PER_PAGE' => 1,

    'TOP_NEWS_SEARCH_BY_YEAR' => 1,

    'TOP_NEWS_SEARCH_BY_MONTH' => 2,

    'TOP_NEWS_SEARCH_BY_TOPIC' => 3,

    'TOP_NEWS_SEARCH_BY_KEYWORD' => 4,

    'TOP_FIVE_PAGINATE' => 100,

    'REPORT_SEARCH_BY_ID' => 1,

    'REPORT_SEARCH_BY_NAME' => 2,

    'REPORT_SEARCH_BY_CATEGORY' => 3,

    'INTERACTIVE_AUTHOR' => 'author_headshot/',

    'INTERACTIVE_COVER' => 'interactive_reports/cover_image/',

    'VIDEO_TYPE_YOUTUBE' => 1,
    'USER_POSITION' => ['1' => 'Executive/Owner', '2' => 'Marketing/PR', '3' => 'Research/Academic',
        '4' => 'Tech/Product Development', '5' => 'Accounting/Legal/Compliance',
        '6' => "Other(please describe)"],

    'INDUSTRY_ROLE' => ['1' => 'Academia/NGO/Non-profit', '2' => 'Alcohol', '3' => 'Cultivation',
        '4' => 'Extraction/Processing',
        '4' => 'Investment/Finacial', '5' => 'Legal/Regulatory', '6' => 'Market Research/Consulting',
        '5' => 'Media/Press/PR', '6' => 'Medical/Healthcare', '7' => 'Pharmaceutical', '8' => 'Retail',
        '9' => 'Tobacoo', '10' => 'Other Cannabis Business'],

    'COMPANY_NEWS_INFORMATION' => ['1' => 'Web', '2' => 'Conference', '3' => 'Speaking Engagement',
        '4' => 'Social Media', '5' => 'Press/Media', '6' => 'Referral', '7' => 'Other (please specify)'
    ],

    'ERROR' => ['PERMISSION_DENIED' => 1, 'SUBSCRIPTION_EXPIRED' => 2],

    'FEEDBACKTOEMAIL' => env('FEEDBACKTOEMAIL'),

    'REPORT_TYPE' => ['REPORTS_ENTERPRISES_PDF' => 1, 'REPORTS_FULL_PDF' => 2, 'REPORTS_FULL_PDF' => 3, 'REPORTS_SUMMERY_PDF' => 4],

    'ELASTIC_SEARCH_URL' => env('ELASTIC_SEARCH_URL', 'elastic.newfrontierdata.com:9200'),

    'REPORT_CATEGORY' => ['Special' => 'Special', 'State' => 'State', 'Premium' => 'Premium'],

    'REPORT_SEGMENT' => [1 => 'Operating', 2 => 'Researching', 3 => 'Investing'],

    'WEEDGUIDE_BASE_URI' => env('WEEDGUIDE_BASE_URI'),

    'WEEDGUIDE_METHOD' => 'GET',

    'WEEDGUIDE_URI' => '/search',

    'WEEDGUIDE_X-API-KEY' => env('WEEDGUIDE_X_API_KEY'),

    'reports_avilable' => ['0' => 'Deactivated', '1' => 'active', '2' => 'Inactive'],

    'AUDIT_IS_OBJECT' => ['REPORT', 'CHART', 'INTERACTIVE_REPORT'],

    'AUDIT_OBJECT' => ['REPORT' => 'reports', 'INTERACTIVE_REPORT' => 'interactive_reports'],

    'AUDIT_REPORT_TYPE' => [1 => 'enterprise_pdf', 2 => 'full_pdf', 3 => 'summary_pdf', 4 => 'summary_pdf'],

    'REPORTS_STORAGE_NEW' => 'storage/reports/',

    'CHARTS_STORAGE_NEW' => 'storage/reports/charts/',

    'PROFILES_STORAGE_NEW' => 'storage/profiles/company/cover/',

    'IMAGE_VIEW' => [1 => 'charts', 2 => 'reports', 3 => 'interactive_reports', 4 => 'profiles', 5 => 'new_features'],

    'WOPRAI_WEEDEX_EXTERNAL_LINK' => 'http://api.woprai.com/',

    'INSIGHT_DAILY_TOPIC_TYPES' => [
        ['name' => 'Financial  ', 'image' => "/storage/top5/category/CannabisInSight-Icons-Finance.png"],
        ['name' => 'Legal  ', 'image' => "/storage/top5/category/CannabisInSight-Icons-Legislative.png"],
        ['name' => 'Wildcard  ', 'image' => "/storage/top5/category/CannabisInSight-Icons-WildCard.png"],
        // ['name' => 'Medical  ', 'image' => "/storage/top5/category/CannabisInSight-Icons-Medical.png"],
        ['name' => 'Social  ', 'image' => "/storage/top5/category/CannabisInSight-Icons-Social&Cultural.png"],
        ['name' => 'Tech, Science & Innovation  ', 'image' => "/storage/top5/category/tech-icon.png"],
        ['name' => 'International  ', 'image' => "/storage/top5/category/Intl-icon.png"],
        ['name' => 'Hemp  ', 'image' => "/storage/top5/category/CannabisInSight-Icons-Hemp.png"],
        ['name' => 'InFocus  ', 'image' => "/storage/top5/category/CannabisInSight-Icons-InFocus.jpg"]
    ],

    'MODULE_SUBCRIPTION_TRACKERS_STATUS' => ['pending' => 0, 'active' => 1, 'cancel' => 2, 'pending_cancel' => 3, 'no_record' => -1],


    'SHORT_POSITION_PLAN_META' => ['single_company' => '1', 'unlimited' => 'unlimited', 'ten_company' => '10', 'three_company' => '3'],

    'Payment_TYPE' => [1 => 'report_payment', 2 => 'subcription__payment'],

    'BASIC_THREE_COMPANIES' => env('BASIC_THREE_COMPANIES'),

    'PLUS_TEN_COMPANIES' => env('PLUS_TEN_COMPANIES'),

    'UNLIMITED_ALL_COMPANIES' => env('UNLIMITED_ALL_COMPANIES'),

    'ADDITIONAL_SINGLE_COMPANY' => env('ADDITIONAL_SINGLE_COMPANY'),

    'SHORT_POSITION_ACTIVITY_LOG_STATUS' => [
        'Purchase' => 'Purchase', 
        'Single_Purchase' => 'Single Purchase', 
        'Upgrade' => 'Upgrade', 
        'Downgrade' => 'Downgrade',
        'Downgrade_trigger' => 'DowngradeTrigger',
        'Cancel_Single_Company_Pending' => 'Cancel Single Company Pending',
        'Canceled_Single_Company' => 'Canceled Single Company',
        'Cancel_Basic_Plan_Pending' => 'Cancel Basic Plan Pending',
        'Cancel_Basic_Subscription' => 'Cancel Basic Subscription',
        'Change_Companies' => 'Change',
        'Auto_RenewalOfPlan' => 'Auto Renewal of a Plan',
    ],

    'SUBCRIPTION_STATUS'=>['downgrade'=>1,'cancel'=>0],

    'ALL_BASIC_PLANS' => [
        'three' => env('BASIC_THREE_COMPANIES'), 
        'ten' => env('PLUS_TEN_COMPANIES'),
        'unlimited' => env('UNLIMITED_ALL_COMPANIES')
    ],

];
