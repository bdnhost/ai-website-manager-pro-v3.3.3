<?php
/**
 * Brand Sample Data
 * דוגמאות JSON למותגים
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Website_Manager_Brand_Samples
{

    /**
     * קבלת כל הדוגמאות
     */
    public static function get_all_samples()
    {
        return [
            'tech_startup' => self::get_tech_startup_sample(),
            'health_wellness' => self::get_health_wellness_sample(),
            'education' => self::get_education_sample(),
            'ecommerce' => self::get_ecommerce_sample(),
            'consulting' => self::get_consulting_sample(),
            'creative' => self::get_creative_sample(),
            'restaurant' => self::get_restaurant_sample(),
            'fitness' => self::get_fitness_sample(),
            'real_estate' => self::get_real_estate_sample(),
            'finance' => self::get_finance_sample()
        ];
    }

    /**
     * דוגמה לסטארט-אפ טכנולוגי
     */
    public static function get_tech_startup_sample()
    {
        return [
            'name' => 'TechFlow Solutions',
            'description' => 'חברת טכנולוגיה חדשנית המתמחה בפתרונות אוטומציה ובינה מלאכותית לעסקים',
            'industry' => 'טכנולוגיה',
            'target_audience' => [
                'demographics' => 'מנהלי טכנולוגיה, יזמים, חברות בינוניות וגדולות',
                'psychographics' => 'אנשים המחפשים חדשנות, יעילות ופתרונות מתקדמים',
                'pain_points' => ['תהליכים ידניים איטיים', 'חוסר אוטומציה', 'עלויות גבוהות'],
                'goals' => ['שיפור יעילות', 'חיסכון בעלויות', 'חדשנות טכנולוגית']
            ],
            'brand_voice' => 'professional',
            'tone_of_voice' => 'inspiring',
            'keywords' => ['טכנולוגיה', 'בינה מלאכותית', 'אוטומציה', 'חדשנות', 'דיגיטל', 'פתרונות'],
            'values' => ['חדשנות', 'איכות', 'מהירות', 'שקיפות', 'מקצועיות'],
            'mission' => 'להוביל את המהפכה הטכנולוגית ולספק פתרונות AI מתקדמים שמשנים את אופן העבודה של עסקים',
            'vision' => 'להיות החברה המובילה בתחום פתרונות הבינה המלאכותית לעסקים בישראל ובעולם',
            'unique_selling_proposition' => 'פתרונות AI מותאמים אישית עם יישום מהיר ותמיכה 24/7',
            'competitor_analysis' => [
                'main_competitors' => ['Microsoft', 'Google Cloud', 'AWS'],
                'competitive_advantages' => ['התמחות בשוק הישראלי', 'תמיכה בעברית', 'יישום מהיר'],
                'market_positioning' => 'פתרונות AI נגישים לעסקים בינוניים'
            ],
            'content_pillars' => [
                'pillar_1' => 'חדשנות טכנולוגית',
                'pillar_2' => 'הצלחות לקוחות',
                'pillar_3' => 'מדריכים וטיפים',
                'pillar_4' => 'מגמות בתחום'
            ],
            'brand_colors' => [
                'primary' => '#667eea',
                'secondary' => '#764ba2',
                'accent' => '#f093fb'
            ],
            'typography' => [
                'primary_font' => 'Roboto',
                'secondary_font' => 'Open Sans'
            ],
            'social_media_links' => [
                'linkedin' => 'https://linkedin.com/company/techflow-solutions',
                'twitter' => 'https://twitter.com/techflow_il',
                'facebook' => 'https://facebook.com/techflowsolutions'
            ],
            'seo_settings' => [
                'focus_keywords' => ['בינה מלאכותית', 'אוטומציה עסקית', 'פתרונות AI'],
                'meta_description_template' => 'TechFlow - פתרונות בינה מלאכותית מתקדמים לעסקים',
                'title_template' => '{title} | TechFlow Solutions'
            ]
        ];
    }

    /**
     * דוגמה לתחום בריאות ורווחה
     */
    public static function get_health_wellness_sample()
    {
        return [
            'name' => 'HealthyLife Wellness',
            'description' => 'מרכז בריאות ורווחה המתמחה בגישה הוליסטית לבריאות הגוף והנפש',
            'industry' => 'בריאות',
            'target_audience' => [
                'demographics' => 'נשים וגברים בגילאי 25-55, משכילים, מעמד בינוני-גבוה',
                'psychographics' => 'אנשים המעוניינים באורח חיים בריא ואיזון בין עבודה לחיים',
                'pain_points' => ['לחץ ועייפות', 'בעיות משקל', 'חוסר זמן לפעילות גופנית'],
                'goals' => ['שיפור הבריאות', 'איזון חיים', 'הרגשה טובה יותר']
            ],
            'brand_voice' => 'friendly',
            'tone_of_voice' => 'educational',
            'keywords' => ['בריאות', 'רווחה', 'תזונה', 'כושר', 'איזון', 'אורח חיים בריא'],
            'values' => ['בריאות', 'טבעיות', 'איזון', 'אכפתיות', 'מקצועיות'],
            'mission' => 'לעזור לאנשים להשיג בריאות ורווחה מיטבית באמצעות גישה הוליסטית ומותאמת אישית',
            'vision' => 'להיות המרכז המוביל לבריאות ורווחה בישראל',
            'unique_selling_proposition' => 'גישה הוליסטית משולבת עם מעקב אישי ותמיכה מתמשכת'
        ];
    }

    /**
     * דוגמה לתחום חינוך
     */
    public static function get_education_sample()
    {
        return [
            'name' => 'EduTech Academy',
            'description' => 'מוסד חינוכי מתקדם המתמחה בהכשרה טכנולוגית ופיתוח כישורים דיגיטליים',
            'industry' => 'חינוך',
            'target_audience' => [
                'demographics' => 'סטודנטים, מקצוענים המעוניינים בהשתלמות, גילאי 18-45',
                'psychographics' => 'אנשים שואפי הצלחה, מעוניינים בלמידה ופיתוח קריירה',
                'pain_points' => ['חוסר כישורים דיגיטליים', 'קושי במציאת עבודה', 'צורך בהשתלמות'],
                'goals' => ['פיתוח קריירה', 'רכישת כישורים חדשים', 'שיפור הכנסות']
            ],
            'brand_voice' => 'authoritative',
            'tone_of_voice' => 'educational',
            'keywords' => ['חינוך', 'הכשרה', 'למידה', 'כישורים', 'קריירה', 'טכנולוגיה'],
            'values' => ['מצוינות', 'למידה', 'התפתחות', 'מקצועיות', 'חדשנות'],
            'mission' => 'לספק חינוך איכותי ומעשי שמכין את הדור הבא לעולם הטכנולוגי',
            'vision' => 'להיות המוסד המוביל בהכשרה טכנולוגית בישראל'
        ];
    }

    /**
     * דוגמה למסחר אלקטרוני
     */
    public static function get_ecommerce_sample()
    {
        return [
            'name' => 'StyleHub Store',
            'description' => 'חנות אונליין מובילה לאופנה ואקססוריז עם מגוון רחב של מותגים איכותיים',
            'industry' => 'מסחר אלקטרוני',
            'target_audience' => [
                'demographics' => 'נשים וגברים בגילאי 20-45, אוהבי אופנה, מעמד בינוני',
                'psychographics' => 'אנשים המעוניינים להיראות טוב, עוקבים אחר מגמות אופנה',
                'pain_points' => ['קושי במציאת בגדים מתאימים', 'מחירים גבוהים', 'איכות לא עקבית'],
                'goals' => ['להיראות טוב', 'למצוא בגדים איכותיים', 'לחסוך כסף']
            ],
            'brand_voice' => 'friendly',
            'tone_of_voice' => 'conversational',
            'keywords' => ['אופנה', 'סטייל', 'בגדים', 'אקססוריז', 'מותגים', 'איכות'],
            'values' => ['איכות', 'סטייל', 'נגישות', 'שירות', 'מגוון'],
            'mission' => 'להנגיש אופנה איכותית לכולם במחירים הוגנים',
            'vision' => 'להיות החנות האונליין המועדפת לאופנה בישראל'
        ];
    }

    /**
     * דוגמה לייעוץ עסקי
     */
    public static function get_consulting_sample()
    {
        return [
            'name' => 'Business Growth Partners',
            'description' => 'חברת ייעוץ עסקי המתמחה באסטרטגיה, צמיחה ושיפור ביצועים עסקיים',
            'industry' => 'ייעוץ',
            'target_audience' => [
                'demographics' => 'בעלי עסקים, מנהלים בכירים, יזמים',
                'psychographics' => 'אנשים שואפי הצלחה, מעוניינים בצמיחה עסקית',
                'pain_points' => ['קושי בצמיחה', 'תחרות קשה', 'חוסר אסטרטגיה ברורה'],
                'goals' => ['צמיחה עסקית', 'שיפור רווחיות', 'יעילות תפעולית']
            ],
            'brand_voice' => 'authoritative',
            'tone_of_voice' => 'informative',
            'keywords' => ['ייעוץ עסקי', 'אסטרטגיה', 'צמיחה', 'ביצועים', 'רווחיות'],
            'values' => ['מקצועיות', 'תוצאות', 'שקיפות', 'מצוינות', 'שותפות'],
            'mission' => 'לעזור לעסקים להשיג את המטרות שלהם ולצמוח בצורה בת קיימא',
            'vision' => 'להיות שותף האסטרטגי המובחר לעסקים מצליחים'
        ];
    }

    /**
     * דוגמה לסטודיו יצירתי
     */
    public static function get_creative_sample()
    {
        return [
            'name' => 'Creative Studio Pro',
            'description' => 'סטודיו יצירתי מתקדם המתמחה בעיצוב גרפי, מיתוג ופתרונות ויזואליים',
            'industry' => 'יצירה ועיצוב',
            'target_audience' => [
                'demographics' => 'עסקים קטנים ובינוניים, יזמים, מנהלי שיווק',
                'psychographics' => 'אנשים המעוניינים בעיצוב איכותי וייחודי',
                'pain_points' => ['עיצוב גנרי', 'חוסר זהות ויזואלית', 'קושי בהבדלה מהתחרות'],
                'goals' => ['זהות ויזואלית חזקה', 'הבדלה מהתחרות', 'מיתוג מקצועי']
            ],
            'brand_voice' => 'casual',
            'tone_of_voice' => 'inspiring',
            'keywords' => ['עיצוב', 'יצירתיות', 'מיתוג', 'ויזואל', 'גרפיקה', 'חדשנות'],
            'values' => ['יצירתיות', 'חדשנות', 'איכות', 'ייחודיות', 'אמנות'],
            'mission' => 'להביא יצירתיות ועיצוב מקצועי לעולם העסקי',
            'vision' => 'להיות הסטודיו היצירתי המוביל בישראל'
        ];
    }
}  
  
    /**
     * דוגמה למסעדה
     */
    public static function get_restaurant_sample() {
        return [
            'name' => 'Taste of Italy',
            'description' => 'מסעדה איטלקית אותנטית המגישה מנות מסורתיות מהמטבח האיטלקי',
            'industry' => 'מזון ומסעדנות',
            'target_audience' => [
                'demographics' => 'משפחות, זוגות, אוהבי אוכל איטלקי, גילאי 25-60',
                'psychographics' => 'אנשים המעוניינים בחוויה קולינרית איכותית ואווירה נעימה',
                'pain_points' => ['קושי במציאת אוכל איטלקי אותנטי', 'מחירים גבוהים', 'שירות לא טוב'],
                'goals' => ['חוויה קולינרית מיוחדת', 'זמן איכות עם המשפחה', 'אוכל טעים ואיכותי']
            ],
            'brand_voice' => 'friendly',
            'tone_of_voice' => 'conversational',
            'keywords' => ['איטלקי', 'פיצה', 'פסטה', 'אותנטי', 'טעים', 'משפחתי'],
            'values' => ['אותנטיות', 'איכות', 'משפחתיות', 'מסורת', 'טעם'],
            'mission' => 'להביא את הטעמים האותנטיים של איטליה לישראל',
            'vision' => 'להיות המסעדה האיטלקית המובילה בעיר',
            'unique_selling_proposition' => 'מתכונים מסורתיים של סבתא איטלקית עם חומרי גלם מיובאים'
        ];
    }
    
    /**
     * דוגמה לחדר כושר
     */
    public static function get_fitness_sample() {
        return [
            'name' => 'FitZone Gym',
            'description' => 'חדר כושר מתקדם עם ציוד חדיש ומגוון רחב של שיעורים קבוצתיים',
            'industry' => 'כושר ובריאות',
            'target_audience' => [
                'demographics' => 'נשים וגברים בגילאי 18-50, פעילים ובריאים',
                'psychographics' => 'אנשים המעוניינים בשמירה על כושר גופני ואורח חיים בריא',
                'pain_points' => ['חוסר מוטיבציה', 'חוסר זמן', 'ציוד לא מתאים'],
                'goals' => ['שיפור הכושר', 'ירידה במשקל', 'בניית שרירים', 'הרגשה טובה']
            ],
            'brand_voice' => 'inspiring',
            'tone_of_voice' => 'motivational',
            'keywords' => ['כושר', 'חדר כושר', 'אימון', 'בריאות', 'שרירים', 'ירידה במשקל'],
            'values' => ['בריאות', 'כושר', 'מוטיבציה', 'הישגיות', 'קהילה'],
            'mission' => 'לעזור לאנשים להשיג את מטרות הכושר שלהם ולחיות חיים בריאים יותר',
            'vision' => 'להיות חדר הכושר המוביל באזור עם הקהילה הכי תומכת'
        ];
    }
    
    /**
     * דוגמה לנדלן
     */
    public static function get_real_estate_sample() {
        return [
            'name' => 'Prime Properties',
            'description' => 'חברת נדלן מובילה המתמחה במכירה ושכירות של נכסים יוקרתיים',
            'industry' => 'נדלן',
            'target_audience' => [
                'demographics' => 'קונים ושוכרים פוטנציאליים, משקיעים, גילאי 25-65',
                'psychographics' => 'אנשים המחפשים נכס איכותי, משקיעים חכמים',
                'pain_points' => ['קושי במציאת נכס מתאים', 'תהליכים מורכבים', 'חוסר שקיפות'],
                'goals' => ['מציאת הבית המושלם', 'השקעה רווחית', 'תהליך חלק ומהיר']
            ],
            'brand_voice' => 'professional',
            'tone_of_voice' => 'trustworthy',
            'keywords' => ['נדלן', 'דירות', 'בתים', 'השקעה', 'מכירה', 'שכירות'],
            'values' => ['מקצועיות', 'אמינות', 'שקיפות', 'שירות', 'מומחיות'],
            'mission' => 'לעזור ללקוחות למצוא את הנכס המושלם ולהשיג את מטרות הנדלן שלהם',
            'vision' => 'להיות חברת הנדלן המובילה והמהימנה ביותר בשוק'
        ];
    }
    
    /**
     * דוגמה לתחום פיננסים
     */
    public static function get_finance_sample() {
        return [
            'name' => 'WealthWise Financial',
            'description' => 'חברת ייעוץ פיננסי המתמחה בניהול השקעות ותכנון פיננסי אישי',
            'industry' => 'פיננסים',
            'target_audience' => [
                'demographics' => 'אנשים עם הכנסה גבוהה, בעלי חסכונות, גילאי 30-65',
                'psychographics' => 'אנשים המעוניינים בביטחון פיננסי ובניית עושר',
                'pain_points' => ['חוסר ידע בהשקעות', 'פחד מסיכונים', 'חוסר זמן לניהול'],
                'goals' => ['ביטחון פיננסי', 'גידול ההון', 'פרישה נוחה', 'העברת עושר']
            ],
            'brand_voice' => 'authoritative',
            'tone_of_voice' => 'trustworthy',
            'keywords' => ['השקעות', 'פיננסים', 'ייעוץ פיננסי', 'עושר', 'פרישה', 'חסכונות'],
            'values' => ['אמינות', 'מקצועיות', 'שקיפות', 'ביטחון', 'מומחיות'],
            'mission' => 'לעזור ללקוחות להשיג ביטחון פיננסי ולבנות עושר לטווח הארוך',
            'vision' => 'להיות היועץ הפיננסי המהימן ביותר עבור משפחות ועסקים'
        ];
    }
    
    /**
     * קבלת דוגמה לפי סוג
     */
    public static function get_sample_by_type($type) {
        $samples = self::get_all_samples();
        return $samples[$type] ?? null;
    }
    
    /**
     * קבלת רשימת סוגי הדוגמאות
     */
    public static function get_sample_types() {
        return [
            'tech_startup' => [
                'name' => 'סטארט-אפ טכנולוגי',
                'icon' => '💻',
                'description' => 'חברת טכנולוגיה חדשנית'
            ],
            'health_wellness' => [
                'name' => 'בריאות ורווחה',
                'icon' => '🏥',
                'description' => 'מרכז בריאות ורווחה'
            ],
            'education' => [
                'name' => 'חינוך והכשרה',
                'icon' => '🎓',
                'description' => 'מוסד חינוכי וקורסים'
            ],
            'ecommerce' => [
                'name' => 'מסחר אלקטרוני',
                'icon' => '🛒',
                'description' => 'חנות אונליין'
            ],
            'consulting' => [
                'name' => 'ייעוץ עסקי',
                'icon' => '💼',
                'description' => 'חברת ייעוץ מקצועית'
            ],
            'creative' => [
                'name' => 'יצירה ועיצוב',
                'icon' => '🎨',
                'description' => 'סטודיו יצירתי'
            ],
            'restaurant' => [
                'name' => 'מסעדה',
                'icon' => '🍝',
                'description' => 'מסעדה איטלקית'
            ],
            'fitness' => [
                'name' => 'כושר ובריאות',
                'icon' => '💪',
                'description' => 'חדר כושר מתקדם'
            ],
            'real_estate' => [
                'name' => 'נדלן',
                'icon' => '🏠',
                'description' => 'חברת נדלן מובילה'
            ],
            'finance' => [
                'name' => 'פיננסים',
                'icon' => '💰',
                'description' => 'ייעוץ פיננסי'
            ]
        ];
    }
}