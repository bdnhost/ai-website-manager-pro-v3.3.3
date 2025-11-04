<?php
/**
 * Content Generator with Integrated SEO Templates
 *
 * @package AI_Manager_Pro
 * @subpackage Modules\Content_Generation
 */

namespace AI_Manager_Pro\Modules\Content_Generation;

use AI_Manager_Pro\Modules\AI_Providers\AI_Provider_Manager;
use AI_Manager_Pro\Modules\Brand_Management\Brand_Manager;
use Monolog\Logger;

/**
 * Content Generator Class with SEO Templates
 */
class Content_Generator {

    /**
     * AI Provider Manager instance
     *
     * @var AI_Provider_Manager
     */
    private $ai_providers;

    /**
     * Brand Manager instance
     *
     * @var Brand_Manager
     */
    private $brand_manager;

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Content templates
     *
     * @var array
     */
    private $content_templates;

    /**
     * SEO templates for structured content
     *
     * @var array
     */
    private $seo_templates;

    /**
     * Constructor
     *
     * @param AI_Provider_Manager $ai_providers AI Provider Manager instance
     * @param Brand_Manager $brand_manager Brand Manager instance
     * @param Logger $logger Logger instance
     */
    public function __construct(AI_Provider_Manager $ai_providers, Brand_Manager $brand_manager, Logger $logger) {
        $this->ai_providers = $ai_providers;
        $this->brand_manager = $brand_manager;
        $this->logger = $logger;
        $this->content_templates = $this->get_content_templates();
        $this->seo_templates = $this->init_seo_templates();

        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_ai_manager_pro_generate_content', [$this, 'handle_generate_content_ajax']);
    }

    /**
     * Initialize SEO templates
     *
     * @return array SEO templates
     */
    private function init_seo_templates() {
        return [
            'article' => $this->get_article_seo_instructions(),
            'guide' => $this->get_guide_seo_instructions(),
            'review' => $this->get_review_seo_instructions(),
            'product' => $this->get_product_seo_instructions(),
            'blog_post' => $this->get_blog_post_seo_instructions()
        ];
    }

    /**
     * Get Article SEO instructions
     */
    private function get_article_seo_instructions() {
        return '

=== דרישות קריטיות למבנה SEO מושלם ===

חובה לפעול לפי המבנה המדויק הזה:

1. התחל בכותרת H1 אחת בלבד (כותרת המאמר הראשית)
   - הכותרת חייבת להיות מושכת, ברורה ולכלול מילות מפתח
   - אורך מומלץ: 50-60 תווים
   - דוגמה: "המדריך המלא לבחירת [נושא] ב-2025"

2. כתוב פתיחה מושכת ומעניינת (2-3 משפטים):
   - הסבר בקצרה על מה המאמר
   - למה זה חשוב לקורא
   - מה הוא ילמד כאן
   - השתמש במשפט וו (hook) שמושך את הקורא

3. צור תוכן עניינים עם קישורים פנימיים:
   <h2>תוכן עניינים</h2>
   <ul>
   <li><a href="#section-1">כותרת הסעיף הראשון</a></li>
   <li><a href="#section-2">כותרת הסעיף השני</a></li>
   <li><a href="#section-3">כותרת הסעיף השלישי</a></li>
   </ul>

4. צור 3-5 סעיפים ראשיים, כאשר כל סעיף כולל:
   - כותרת H2 עם תכונת id (לדוגמה: <h2 id="section-1">כותרת הסעיף</h2>)
   - פסקת פתיחה בת 2-3 משפטים המסבירה על מה הסעיף
   - 2-3 תתי-סעיפים עם כותרות H3 תחת כל H2
   - לכל H3 תוכן מפורט בן 3-4 פסקאות
   - לפחות טבלת השוואה אחת או טבלת נתונים באמצעות HTML תקני:
     <table>
     <thead><tr><th>עמודה 1</th><th>עמודה 2</th><th>עמודה 3</th></tr></thead>
     <tbody>
     <tr><td>נתון 1</td><td>נתון 2</td><td>נתון 3</td></tr>
     <tr><td>נתון 4</td><td>נתון 5</td><td>נתון 6</td></tr>
     </tbody>
     </table>
   - השתמש ברשימות (ul/ol) בצורה חכמה להדגשת נקודות

5. הוסף חלק שאלות נפוצות (FAQ):
   <h2>שאלות נפוצות</h2>
   <h3>שאלה 1: [שאלה ממוקדת]?</h3>
   <p>תשובה מפורטת ומקיפה שעונה על השאלה בצורה ברורה...</p>
   <h3>שאלה 2: [שאלה ממוקדת]?</h3>
   <p>תשובה מפורטת...</p>

   צור לפחות 5-7 שאלות נפוצות עם תשובות מפורטות

6. סיים עם סיכום ומסקנות:
   <h2>סיכום ומסקנות</h2>
   <p>סכם את הנקודות המרכזיות של המאמר...</p>
   <p>הוסף קריאה לפעולה (call to action) רלוונטית...</p>

כללי עיצוב וכתיבה:
- השתמש ב-<strong> למונחים חשובים וביטויי מפתח
- השתמש ב-<ul><li> לרשימות תבליטים
- השתמש ב-<ol><li> לרשימות ממוספרות
- כלול נתונים קונקרטיים, סטטיסטיקות ודוגמאות ספציפיות
- הדגש ביטויי מפתח באמצעות <strong>
- הוסף איקונים רלוונטיים (✓, ✅, ❌, 📊, 💡, ⚠️) לשיפור חוויית הקריאה
- כל פסקה צריכה להיות בת 2-4 משפטים (לא יותר מדי ארוכה)
- השתמש בשפה פשוטה, ברורה ונגישה

דגשים חשובים ל-SEO:
- ודא שיש כותרת H1 אחת בלבד
- לפחות 3 כותרות H2
- כל H2 צריך לפחות 2 כותרות H3
- לפחות טבלה אחת במאמר
- לפחות 5 שאלות נפוצות
- אורך מומלץ: 1500-2500 מילים
';
    }

    /**
     * Get Guide SEO instructions
     */
    private function get_guide_seo_instructions() {
        return '

=== דרישות קריטיות למבנה מדריכי הדרכה SEO ===

חובה לפעול לפי המבנה המדויק הזה:

1. כותרת H1: "איך ל[פעולה]: מדריך מלא צעד אחר צעד [שנה]"
   - הכותרת חייבת לכלול את המילה "איך" או "כיצד"
   - דוגמה: "כיצד ליצור אתר וורדפרס: מדריך מלא צעד אחר צעד 2025"
   - אורך מומלץ: 50-60 תווים

2. פתיחה קצרה ומעניינת (2-3 משפטים):
   - מה הם ילמדו במדריך הזה
   - למה זה חשוב
   - כמה זמן ייקח להם
   - דוגמה: "במדריך זה תלמדו כיצד ליצור אתר וורדפרס מקצועי תוך 30 דקות, גם אם אין לכם ניסיון קודם. נלמד את כל השלבים הנדרשים, ממציאת אחסון ועד פרסום האתר הראשון."

3. תוכן עניינים מפורט:
   <h2>תוכן עניינים</h2>
   <ul>
   <li><a href="#requirements">מה תצטרכו</a></li>
   <li><a href="#step-by-step">המדריך צעד אחר צעד</a></li>
   <li><a href="#mistakes">טעויות נפוצות</a></li>
   <li><a href="#tips">טיפים מקצועיים</a></li>
   <li><a href="#faq">שאלות נפוצות</a></li>
   </ul>

4. רשימת דרישות וכלים נדרשים:
   <h2 id="requirements">מה תצטרכו להתחלה</h2>
   <ul>
   <li>✅ <strong>דרישה 1:</strong> הסבר מפורט מדוע זה נדרש</li>
   <li>✅ <strong>דרישה 2:</strong> הסבר מפורט</li>
   <li>✅ <strong>דרישה 3:</strong> הסבר מפורט</li>
   </ul>
   <p><strong>💰 עלות משוערת:</strong> ₪XX-XX</p>
   <p><strong>⏱️ זמן משוער:</strong> XX דקות</p>

5. מדריך צעד אחר צעד מפורט:
   <h2 id="step-by-step">המדריך צעד אחר צעד</h2>

   <h3>שלב 1: [שם הפעולה הראשונה]</h3>
   <p>הסבר מפורט על מה נעשה בשלב הזה ולמה זה חשוב...</p>
   <ol>
   <li><strong>תת-שלב 1.1:</strong> הוראות מפורטות ומדויקות</li>
   <li><strong>תת-שלב 1.2:</strong> הוראות מפורטות ומדויקות</li>
   <li><strong>תת-שלב 1.3:</strong> הוראות מפורטות ומדויקות</li>
   </ol>
   <p>💡 <strong>טיפ:</strong> עצה מועילה לשלב הזה</p>
   <p>⏱️ <strong>זמן משוער:</strong> X דקות</p>
   <p>⚠️ <strong>שימו לב:</strong> אזהרה או דגש חשוב</p>

   <h3>שלב 2: [שם הפעולה השנייה]</h3>
   <p>הסבר מפורט...</p>
   <ol>
   <li><strong>תת-שלב 2.1:</strong> הוראות מפורטות</li>
   <li><strong>תת-שלב 2.2:</strong> הוראות מפורטות</li>
   </ol>
   <p>💡 <strong>טיפ:</strong> עצה מועילה</p>
   <p>⏱️ <strong>זמן משוער:</strong> X דקות</p>

   חזור על התבנית הזו עבור 5-8 שלבים סה"כ

6. טעויות נפוצות שחובה להימנע מהן:
   <h2 id="mistakes">טעויות נפוצות שכדאי להימנע מהן</h2>
   <ul>
   <li>❌ <strong>טעות 1:</strong> תיאור הטעות ומדוע היא בעייתית</li>
   <li>✅ <strong>הפתרון:</strong> מה לעשות במקום</li>
   <li>❌ <strong>טעות 2:</strong> תיאור הטעות</li>
   <li>✅ <strong>הפתרון:</strong> מה לעשות במקום</li>
   </ul>

   כלול לפחות 5 טעויות נפוצות עם פתרונות

7. טיפים מקצועיים בטבלה:
   <h2 id="tips">טיפים מקצועיים</h2>
   <table>
   <thead>
   <tr>
   <th>💡 טיפ</th>
   <th>📝 תיאור מפורט</th>
   <th>📈 השפעה</th>
   </tr>
   </thead>
   <tbody>
   <tr>
   <td><strong>טיפ 1</strong></td>
   <td>הסבר מפורט של הטיפ וכיצד ליישם אותו</td>
   <td>תיאור התועלת והשיפור שיגיע מהטיפ</td>
   </tr>
   <tr>
   <td><strong>טיפ 2</strong></td>
   <td>הסבר מפורט</td>
   <td>תיאור התועלת</td>
   </tr>
   </tbody>
   </table>

   כלול לפחות 5 טיפים מקצועיים

8. שאלות נפוצות:
   <h2 id="faq">שאלות נפוצות</h2>
   <h3>שאלה 1: [שאלה רלוונטית למדריך]?</h3>
   <p>תשובה מפורטת ומקיפה...</p>

   כלול לפחות 5-7 שאלות נפוצות

9. סיכום והמלצות:
   <h2>סיכום ומה הלאה</h2>
   <p>סיכום של כל השלבים שעברנו...</p>
   <p>המלצות לצעדים הבאים...</p>
   <p>קריאה לפעולה...</p>

חובה לכלול:
- הערכות זמן לכל שלב
- רשימות ממוספרות (ol) לשלבים
- טבלת טיפים מקצועיים
- לפחות 5 טעויות נפוצות עם פתרונות
- איקונים רלוונטיים (✅, ❌, 💡, ⏱️, ⚠️, 📊, 💰)
- הסברים מפורטים ומעשיים
- אורך מומלץ: 1800-2800 מילים
';
    }

    /**
     * Get Review SEO instructions
     */
    private function get_review_seo_instructions() {
        return '

=== דרישות קריטיות למבנה ביקורות SEO ===

חובה לפעול לפי המבנה המדויק הזה:

1. כותרת H1: "[שם המוצר] ביקורת מקיפה [שנה]: האם כדאי לקנות?"
   - הכותרת חייבת לכלול את שם המוצר ואת השנה
   - דוגמה: "iPhone 15 Pro ביקורת מקיפה 2025: האם כדאי לקנות?"
   - אורך מומלץ: 50-70 תווים

2. פתיחה עם פסק דין מהיר (2-3 משפטים):
   - מה המוצר
   - הרושם הראשוני
   - המסקנה התמציתית (כדאי/לא כדאי)
   - דוגמה: "ה-iPhone 15 Pro הוא הסמארטפון הכי מתקדם של אפל עד היום. לאחר 3 שבועות של שימוש יומיומי, אני יכול לומר שזה שדרוג משמעותי, אבל לא לכולם. בואו נבדוק לעומק."

3. סקירה מהירה בטבלה:
   <h2>📊 סקירה מהירה</h2>
   <table>
   <tbody>
   <tr><th>שם המוצר</th><td>[שם מלא]</td></tr>
   <tr><th>יצרן</th><td>[שם היצרן]</td></tr>
   <tr><th>קטגוריה</th><td>[קטגוריה]</td></tr>
   <tr><th>מחיר</th><td>₪XX,XXX</td></tr>
   <tr><th>דירוג כולל</th><td>★★★★☆ (4.2/5)</td></tr>
   <tr><th>האם מומלץ</th><td>✅ כן / ❌ לא</td></tr>
   </tbody>
   </table>

4. יתרונות וחסרונות בטבלה מפורטת:
   <h2>⚖️ יתרונות וחסרונות</h2>
   <table>
   <thead>
   <tr>
   <th style="width: 50%;">יתרונות ✅</th>
   <th style="width: 50%;">חסרונות ❌</th>
   </tr>
   </thead>
   <tbody>
   <tr>
   <td>
   <ul>
   <li><strong>יתרון 1:</strong> הסבר מפורט מדוע זה יתרון</li>
   <li><strong>יתרון 2:</strong> הסבר מפורט</li>
   <li><strong>יתרון 3:</strong> הסבר מפורט</li>
   </ul>
   </td>
   <td>
   <ul>
   <li><strong>חיסרון 1:</strong> הסבר מפורט מדוע זה חיסרון</li>
   <li><strong>חיסרון 2:</strong> הסבר מפורט</li>
   <li><strong>חיסרון 3:</strong> הסבר מפורט</li>
   </ul>
   </td>
   </tr>
   </tbody>
   </table>

   כלול לפחות 5 יתרונות ו-3 חסרונות

5. ניתוח מפורט של תכונות עיקריות:
   <h2>🔍 ניתוח מפורט</h2>

   <h3>תכונה 1: [שם התכונה]</h3>
   <p>ניתוח מעמיק של התכונה, כיצד היא עובדת, מה היא מציעה, וכיצד היא משתווה למתחרים...</p>
   <p><strong>📊 דירוג: 8.5/10</strong></p>
   <p><strong>💡 מה אהבנו:</strong> פירוט נקודות חיוביות</p>
   <p><strong>⚠️ מה חסר:</strong> פירוט נקודות שניתן לשפר</p>

   <h3>תכונה 2: [שם התכונה]</h3>
   <p>ניתוח מפורט...</p>
   <p><strong>📊 דירוג: 9/10</strong></p>
   <p><strong>💡 מה אהבנו:</strong> פירוט</p>
   <p><strong>⚠️ מה חסר:</strong> פירוט</p>

   חזור על התבנית הזו עבור 4-6 תכונות עיקריות

6. השוואה עם מתחרים:
   <h2>🆚 השוואה עם מתחרים מובילים</h2>
   <table>
   <thead>
   <tr>
   <th>תכונה</th>
   <th>[המוצר]</th>
   <th>מתחרה 1</th>
   <th>מתחרה 2</th>
   </tr>
   </thead>
   <tbody>
   <tr>
   <td><strong>מחיר</strong></td>
   <td>₪X,XXX</td>
   <td>₪Y,YYY</td>
   <td>₪Z,ZZZ</td>
   </tr>
   <tr>
   <td><strong>תכונה A</strong></td>
   <td>✅ כן</td>
   <td>❌ לא</td>
   <td>✅ כן</td>
   </tr>
   <tr>
   <td><strong>תכונה B</strong></td>
   <td>⭐⭐⭐⭐⭐</td>
   <td>⭐⭐⭐</td>
   <td>⭐⭐⭐⭐</td>
   </tr>
   <tr>
   <td><strong>ביצועים</strong></td>
   <td>9/10</td>
   <td>7/10</td>
   <td>8/10</td>
   </tr>
   </tbody>
   </table>

7. פסק הדין המקיף שלנו:
   <h2>⚖️ פסק הדין הסופי שלנו</h2>

   <p><strong>דירוג כולל: ★★★★☆ (4.2/5)</strong></p>

   <h3>ציונים לפי קטגוריות:</h3>
   <ul>
   <li>🎯 <strong>תכונות ויכולות:</strong> 8.5/10 - הסבר קצר</li>
   <li>🎨 <strong>עיצוב ובנייה:</strong> 9/10 - הסבר קצר</li>
   <li>⚡ <strong>ביצועים:</strong> 8/10 - הסבר קצר</li>
   <li>💰 <strong>תמורה למחיר:</strong> 7.5/10 - הסבר קצר</li>
   <li>👥 <strong>קלות שימוש:</strong> 9/10 - הסבר קצר</li>
   <li>🛠️ <strong>תמיכה ושירות:</strong> 8/10 - הסבר קצר</li>
   </ul>

8. למי זה מתאים:
   <h2>👥 למי המוצר הזה מתאים?</h2>

   <h3>✅ כדאי לקנות אם:</h3>
   <ul>
   <li>אתם מחפשים [תיאור צורך 1]</li>
   <li>אתם זקוקים ל[תיאור צורך 2]</li>
   <li>התקציב שלכם הוא [טווח מחירים]</li>
   </ul>

   <h3>❌ אל תקנו אם:</h3>
   <ul>
   <li>אתם מחפשים [תיאור מה שהמוצר לא מספק]</li>
   <li>התקציב שלכם מוגבל ל[סכום נמוך יותר]</li>
   <li>אתם זקוקים ל[תכונה שהמוצר לא מציע]</li>
   </ul>

9. שאלות נפוצות:
   <h2>❓ שאלות נפוצות</h2>
   <h3>שאלה 1: [שאלה נפוצה על המוצר]?</h3>
   <p>תשובה מפורטת המבוססת על הניסיון שלנו...</p>

   כלול לפחות 7-10 שאלות נפוצות

10. המלצה סופית:
    <h2>🎯 ההמלצה הסופית שלנו</h2>
    <p>סיכום מקיף של כל הביקורת...</p>
    <p>המלצה ברורה: האם כדאי לקנות או לא...</p>
    <p>אלטרנטיבות אם המוצר לא מתאים...</p>

חובה לכלול:
- דירוגים עם כוכבים (★★★★☆)
- טבלת יתרונות וחסרונות
- טבלת השוואה למתחרים
- ציון כולל מחושב
- לפחות 4-6 תכונות עיקריות עם דירוג
- סעיף "למי זה מתאים"
- לפחות 7 שאלות נפוצות
- המלצה ברורה וחד-משמעית
- אורך מומלץ: 2000-3000 מילים
';
    }

    /**
     * Get Product SEO instructions
     */
    private function get_product_seo_instructions() {
        return '

=== דרישות קריטיות למבנה תיאורי מוצר SEO ===

חובה לפעול לפי המבנה המדויק הזה:

1. כותרת H1: "[שם המוצר] - [יתרון עיקרי/USP]"
   - הכותרת חייבת לכלול את שם המוצר והערך המוצע
   - דוגמה: "רובוט שואב Roomba S9+ - ניקוי חכם ואוטומטי לחלוטין"
   - אורך מומלץ: 50-70 תווים

2. תיאור מושך וקצר (2-3 משפטים):
   - מה המוצר עושה
   - למה הוא מיוחד
   - מי צריך את זה
   - דוגמה: "רובוט השואב Roomba S9+ הוא המכשיר החכם ביותר לניקוי הבית. עם תחנת ריקון אוטומטית ומיפוי חכם, הוא מנקה את הבית לבד ללא התערבותכם. פתרון מושלם למי שרוצה בית נקי עם אפס מאמץ."

3. תכונות עיקריות במבט חטוף:
   <h2>✨ תכונות עיקריות במבט חטוף</h2>
   <ul>
   <li>✅ <strong>תכונה 1:</strong> הסבר קצר של התועלת הישירה</li>
   <li>✅ <strong>תכונה 2:</strong> הסבר קצר של התועלת</li>
   <li>✅ <strong>תכונה 3:</strong> הסבר קצר של התועלת</li>
   <li>✅ <strong>תכונה 4:</strong> הסבר קצר של התועלת</li>
   <li>✅ <strong>תכונה 5:</strong> הסבר קצר של התועלת</li>
   </ul>

   כלול לפחות 6-8 תכונות עיקריות

4. מה כלול באריזה:
   <h2>📦 מה כלול באריזה</h2>
   <ul>
   <li>✓ פריט 1 - תיאור קצר</li>
   <li>✓ פריט 2 - תיאור קצר</li>
   <li>✓ פריט 3 - תיאור קצר</li>
   <li>✓ פריט 4 - תיאור קצר</li>
   </ul>

5. תכונות מפורטות עם הסברים:
   <h2>🔍 תכונות מפורטות</h2>

   <h3>תכונה 1: [שם התכונה]</h3>
   <p><strong>💡 מה זה עושה:</strong> הסבר מפורט של איך התכונה עובדת ומה היא מספקת</p>
   <p><strong>🎯 למה זה חשוב:</strong> הסבר של התועלת המעשית והערך שהתכונה מביאה</p>
   <p><strong>⚡ דוגמה לשימוש:</strong> תרחיש שימוש קונקרטי</p>

   <h3>תכונה 2: [שם התכונה]</h3>
   <p><strong>💡 מה זה עושה:</strong> הסבר מפורט</p>
   <p><strong>🎯 למה זה חשוב:</strong> הסבר התועלת</p>
   <p><strong>⚡ דוגמה לשימוש:</strong> תרחיש שימוש</p>

   חזור על התבנית הזו עבור 5-7 תכונות

6. מפרטים טכניים מלאים:
   <h2>📋 מפרטים טכניים</h2>
   <table>
   <tbody>
   <tr><th>📏 מידות</th><td>XX x YY x ZZ ס"מ</td></tr>
   <tr><th>⚖️ משקל</th><td>X.X ק"ג</td></tr>
   <tr><th>🎨 צבעים זמינים</th><td>שחור, לבן, כסוף</td></tr>
   <tr><th>🔌 ספק כוח</th><td>XX וולט / XX וואט</td></tr>
   <tr><th>🔋 סוללה</th><td>XX mAh - זמן עבודה XX שעות</td></tr>
   <tr><th>📦 חומרי גלם</th><td>[חומר] איכותי</td></tr>
   <tr><th>🛡️ אחריות</th><td>X שנים - אחריות יצרן מלאה</td></tr>
   <tr><th>🏭 ארץ ייצור</th><td>[מדינה]</td></tr>
   <tr><th>📱 תאימות</th><td>iOS, Android, Windows</td></tr>
   </tbody>
   </table>

7. תרחישי שימוש מעשיים:
   <h2>💼 תרחישי שימוש בחיים האמיתיים</h2>

   <h3>תרחיש 1: [שם התרחיש]</h3>
   <p><strong>המצב:</strong> תיאור הבעיה או הצורך</p>
   <p><strong>הפתרון:</strong> כיצד המוצר פותר את זה בצורה מעשית</p>
   <p><strong>התוצאה:</strong> מה המשתמש משיג</p>

   <h3>תרחיש 2: [שם התרחיש]</h3>
   <p><strong>המצב:</strong> תיאור</p>
   <p><strong>הפתרון:</strong> כיצד המוצר עוזר</p>
   <p><strong>התוצאה:</strong> התועלת</p>

   כלול לפחות 3-4 תרחישי שימוש שונים

8. השוואה למתחרים:
   <h2>🆚 מה מייחד את המוצר שלנו</h2>
   <table>
   <thead>
   <tr>
   <th>תכונה</th>
   <th>המוצר שלנו</th>
   <th>מתחרה A</th>
   <th>מתחרה B</th>
   </tr>
   </thead>
   <tbody>
   <tr>
   <td><strong>מחיר</strong></td>
   <td>₪XXX</td>
   <td>₪YYY</td>
   <td>₪ZZZ</td>
   </tr>
   <tr>
   <td><strong>תכונה 1</strong></td>
   <td>✅ כן</td>
   <td>❌ לא</td>
   <td>⚠️ חלקי</td>
   </tr>
   <tr>
   <td><strong>תכונה 2</strong></td>
   <td>✅ מתקדם</td>
   <td>⚠️ בסיסי</td>
   <td>✅ מתקדם</td>
   </tr>
   <tr>
   <td><strong>אחריות</strong></td>
   <td>3 שנים</td>
   <td>1 שנה</td>
   <td>2 שנים</td>
   </tr>
   <tr>
   <td><strong>תמיכה</strong></td>
   <td>24/7 בעברית</td>
   <td>אימייל בלבד</td>
   <td>שעות משרד</td>
   </tr>
   </tbody>
   </table>

9. ערבויות ואמון:
   <h2>🛡️ ערבות לאיכות ושירות</h2>
   <ul>
   <li>✅ <strong>אחריות מלאה ל-X שנים:</strong> כיסוי מלא לכל תקלה או פגם ייצור</li>
   <li>✅ <strong>החזר כספי מלא תוך 30 יום:</strong> לא מרוצה? החזר מלא ללא שאלות</li>
   <li>✅ <strong>משלוח חינם:</strong> משלוח מהיר עד הבית ללא עלות נוספת</li>
   <li>✅ <strong>תמיכה טכנית 24/7:</strong> צוות מקצועי זמין לכל שאלה</li>
   <li>✅ <strong>אישור תקני ISO:</strong> עומד בכל תקני האיכות הבינלאומיים</li>
   <li>✅ <strong>אחריות מורחבת זמינה:</strong> אפשרות להאריך עד 5 שנים</li>
   </ul>

10. המלצות לקוחות:
    <h2>💬 מה לקוחות אומרים</h2>
    <blockquote>
    <p>"ציטוט של לקוח מרוצה המתאר את החוויה שלו..."</p>
    <p><strong>- שם הלקוח, מקצוע</strong></p>
    </blockquote>

    כלול לפחות 3 המלצות לקוחות

11. שאלות נפוצות:
    <h2>❓ שאלות נפוצות</h2>
    <h3>שאלה 1: [שאלה על המוצר]?</h3>
    <p>תשובה מפורטת...</p>

    כלול לפחות 7-10 שאלות נפוצות

12. קריאה לפעולה:
    <h2>🛒 מוכנים להזמין?</h2>
    <p>סיכום היתרונות העיקריים...</p>
    <p><strong>מחיר מיוחד:</strong> ₪XXX (במקום ₪YYY) - חיסכון של XX%!</p>
    <p>📦 <strong>משלוח:</strong> 2-4 ימי עסקים</p>
    <p>💳 <strong>אפשרויות תשלום:</strong> כרטיס אשראי, PayPal, תשלומים</p>

חובה לכלול:
- רשימת תכונות עם ✅ סימון
- טבלת מפרטים טכניים מפורטת
- טבלת השוואה למתחרים
- לפחות 3 תרחישי שימוש מעשיים
- אלמנטים של אמון (אחריות, החזר כספי)
- המלצות לקוחות
- לפחות 7 שאלות נפוצות
- קריאה ברורה לפעולה עם פרטי מחיר
- אורך מומלץ: 1500-2200 מילים
';
    }

    /**
     * Get Blog Post SEO instructions
     */
    private function get_blog_post_seo_instructions() {
        return '

=== דרישות קריטיות למבנה פוסטים בבלוג SEO ===

חובה לפעול לפי המבנה המדויק הזה:

1. כותרת H1 אחת בלבד (מושכת ועשירה במילות מפתח):
   - הכותרת חייבת למשוך תשומת לב ולכלול מילות מפתח
   - השתמש במספרים, שאלות, או הבטחות
   - דוגמאות טובות:
     * "7 טעויות שכולם עושים ב[נושא] (ואיך להימנע מהן)"
     * "המדריך הסופי ל[נושא]: כל מה שצריך לדעת ב-2025"
     * "למה [נושא] חשוב יותר ממה שחשבתם (עם דוגמאות)"
   - אורך מומלץ: 50-70 תווים

2. פתיחה מושכת עם משפט וו (2-3 משפטים):
   - משפט ראשון: עובדה מפתיעה, שאלה מעניינת, או סטטיסטיקה מעניינת
   - משפט שני: למה זה רלוונטי לקורא
   - משפט שלישי: מה הוא ילמד בפוסט הזה
   - דוגמה: "האם ידעתם ש-80% מהעסקים קטנים נכשלים בשנה הראשונה? הסיבה העיקרית? חוסר תכנון נכון. במאמר זה תגלו 7 אסטרטגיות שיבטיחו שאתם בין ה-20% שמצליחים."

3. תוכן עניינים (אופציונלי לפוסטים ארוכים מ-1000 מילים):
   <h2>תוכן עניינים</h2>
   <ul>
   <li><a href="#section-1">סעיף ראשון</a></li>
   <li><a href="#section-2">סעיף שני</a></li>
   <li><a href="#section-3">סעיף שלישי</a></li>
   </ul>

4. 3-4 סעיפים ראשיים, כל אחד עם:

   <h2 id="section-1">כותרת הסעיף הראשון</h2>
   <p>פסקת פתיחה המסבירה על מה הסעיף...</p>

   <h3>תת-סעיף 1.1: [נושא ספציפי]</h3>
   <p>תוכן מפורט עם דוגמאות קונקרטיות...</p>
   <p>השתמש ב-<strong>הדגשות</strong> לנקודות חשובות.</p>

   <h3>תת-סעיף 1.2: [נושא ספציפי]</h3>
   <p>תוכן נוסף...</p>
   <ul>
   <li>נקודה חשובה 1</li>
   <li>נקודה חשובה 2</li>
   <li>נקודה חשובה 3</li>
   </ul>

   <h3>תת-סעיף 1.3: [נושא ספציפי]</h3>
   <p>תוכן עם רשימה ממוספרת:</p>
   <ol>
   <li><strong>שלב ראשון:</strong> הסבר מפורט</li>
   <li><strong>שלב שני:</strong> הסבר מפורט</li>
   <li><strong>שלב שלישי:</strong> הסבר מפורט</li>
   </ol>

   חזור על המבנה הזה עבור 3-4 סעיפים ראשיים (H2)

5. הוסף לפחות טבלה אחת:
   - טבלת השוואה
   - טבלת נתונים
   - טבלת יתרונות וחסרונות
   - דוגמה:

   <table>
   <thead>
   <tr>
   <th>קריטריון</th>
   <th>אופציה A</th>
   <th>אופציה B</th>
   <th>אופציה C</th>
   </tr>
   </thead>
   <tbody>
   <tr>
   <td><strong>עלות</strong></td>
   <td>₪XXX</td>
   <td>₪YYY</td>
   <td>₪ZZZ</td>
   </tr>
   <tr>
   <td><strong>זמן ביצוע</strong></td>
   <td>X שעות</td>
   <td>Y שעות</td>
   <td>Z שעות</td>
   </tr>
   </tbody>
   </table>

6. אזכור קישורים פנימיים (לנושאים קשורים):
   כאשר מתאים, הזכר נושאים קשורים באמצעות:
   [קישור פנימי: נושא קשור]

   דוגמה:
   <p>אם אתם מעוניינים ללמוד יותר על [נושא קשור], קראו את [קישור פנימי: המדריך המלא ל-XXX].</p>

7. טיפים או תובנות מיוחדות (אופציונלי):
   <div style="background: #f0f8ff; padding: 15px; border-left: 4px solid #0073aa; margin: 20px 0;">
   <p>💡 <strong>טיפ מקצועי:</strong> עצה מועילה או תובנה חשובה...</p>
   </div>

8. סיכום ומסקנות:
   <h2>סיכום: המסקנות המרכזיות</h2>
   <p>סיכום של הנקודות העיקריות:</p>
   <ul>
   <li>✅ מסקנה ראשונה</li>
   <li>✅ מסקנה שנייה</li>
   <li>✅ מסקנה שלישית</li>
   </ul>
   <p>קריאה לפעולה: מה הקורא צריך לעשות עכשיו...</p>

כללי כתיבה חשובים:
- השתמש ב-<strong> למונחים חשובים וביטויי מפתח
- השתמש ברשימות (ul/ol) להדגשת נקודות
- כלול דוגמאות קונקרטיות ומעשיות בכל סעיף
- הוסף איקונים רלוונטיים (✓, ✅, ❌, 💡, ⚠️, 📊) לשיפור חוויית הקריאה
- פסקאות קצרות: 2-4 משפטים בלבד
- שפה פשוטה, ברורה ונגישה
- השתמש בשאלות כדי לשמור על מעורבות הקורא
- הוסף נתונים, סטטיסטיקות, או מחקרים כשמתאים

דגשים ל-SEO:
- כותרת H1 אחת בלבד
- 3-4 כותרות H2
- כל H2 עם 2-3 כותרות H3
- לפחות טבלה אחת
- לפחות 2 רשימות (ul או ol)
- קישורים פנימיים לתכנים קשורים
- אורך מומלץ: 800-1500 מילים
- מילות מפתח מפוזרות באופן טבעי לאורך הטקסט
';
    }

    /**
     * Get content templates
     *
     * @return array Content templates
     */
    private function get_content_templates() {
        return [
            'blog_post' => [
                'name' => 'Blog Post',
                'description' => 'Generate a comprehensive blog post',
                'system_prompt' => 'You are a professional content writer creating engaging, SEO-optimized blog posts with proper HTML structure.',
                'user_prompt_template' => 'Write a comprehensive blog post about "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Target Audience: {target_audience}
- Brand Voice: {brand_voice}
- Key Messages: {key_messages}

Requirements:
- Length: {content_length}
- Tone: {tone}
- Include relevant examples and actionable insights
- Optimize for SEO with keywords: {seo_keywords}
- Use proper HTML structure with headings and tables

Please create an engaging, informative blog post that aligns with the brand voice and resonates with the target audience.',
                'default_options' => [
                    'max_tokens' => 2500,
                    'temperature' => 0.7
                ]
            ],
            'article' => [
                'name' => 'Article',
                'description' => 'Generate comprehensive SEO-optimized article',
                'system_prompt' => 'You are a professional content writer creating in-depth, SEO-optimized articles with perfect HTML structure including tables, lists, and proper headings.',
                'user_prompt_template' => 'Write a comprehensive article about "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Target Audience: {target_audience}
- Brand Voice: {brand_voice}
- Key Messages: {key_messages}

Requirements:
- Length: {content_length}
- Tone: {tone}
- SEO keywords: {seo_keywords}
- Include data, statistics, and expert insights
- Create comprehensive coverage of the topic
- Use proper HTML structure

Create an authoritative, well-researched article that provides value and ranks well in search results.',
                'default_options' => [
                    'max_tokens' => 3500,
                    'temperature' => 0.6
                ]
            ],
            'guide' => [
                'name' => 'How-To Guide',
                'description' => 'Generate step-by-step how-to guide',
                'system_prompt' => 'You are an expert at creating clear, actionable how-to guides with perfect HTML structure, numbered steps, tables, and detailed instructions.',
                'user_prompt_template' => 'Create a detailed how-to guide for "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Target Audience: {target_audience}
- Brand Voice: {brand_voice}

Requirements:
- Clear step-by-step instructions with HTML structure
- Practical and actionable
- Length: {content_length}
- Tone: {tone}
- SEO keywords: {seo_keywords}
- Include prerequisites, tools needed, and time estimates
- Add tips, warnings, and common mistakes

Create a comprehensive guide that helps users successfully complete the task.',
                'default_options' => [
                    'max_tokens' => 3500,
                    'temperature' => 0.5
                ]
            ],
            'review' => [
                'name' => 'Product/Service Review',
                'description' => 'Generate detailed product or service review',
                'system_prompt' => 'You are a professional reviewer creating honest, in-depth reviews with perfect HTML structure including comparison tables, pros/cons tables, and ratings.',
                'user_prompt_template' => 'Write a comprehensive review of "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Target Audience: {target_audience}
- Brand Voice: {brand_voice}

Requirements:
- Honest and balanced assessment with proper HTML structure
- Include pros/cons table and comparison tables
- Detailed feature analysis
- Length: {content_length}
- Tone: {tone}
- SEO keywords: {seo_keywords}
- Include ratings with stars (★) and scores (X/10)

Create a thorough review that helps readers make informed decisions.',
                'default_options' => [
                    'max_tokens' => 4000,
                    'temperature' => 0.6
                ]
            ],
            'product' => [
                'name' => 'Product Description',
                'description' => 'E-commerce product description',
                'system_prompt' => 'You are a copywriter creating compelling product descriptions with proper HTML structure including feature lists, specification tables, and comparison tables.',
                'user_prompt_template' => 'Write a product description for "{product_name}" by {brand_name}.

Product Details:
{product_details}

Brand Context:
- Industry: {industry}
- Brand Voice: {brand_voice}
- Target Audience: {target_audience}

Requirements:
- Highlight key features with checkmarks (✅)
- Include specifications table
- Address customer pain points
- Include compelling call-to-action
- Tone: {tone}
- SEO keywords: {seo_keywords}
- Use proper HTML structure

Create a persuasive product description with tables and lists.',
                'default_options' => [
                    'max_tokens' => 2000,
                    'temperature' => 0.6
                ]
            ],
            'social_media' => [
                'name' => 'Social Media Post',
                'description' => 'Generate social media content',
                'system_prompt' => 'You are a social media expert creating engaging posts for various platforms.',
                'user_prompt_template' => 'Create a {platform} post about "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Brand Voice: {brand_voice}
- Target Audience: {target_audience}
- Hashtags: {hashtags}

Requirements:
- Platform: {platform}
- Tone: {tone}
- Include relevant hashtags
- Engaging and shareable

Create a compelling social media post that drives engagement.',
                'default_options' => [
                    'max_tokens' => 500,
                    'temperature' => 0.8
                ]
            ],
            'newsletter' => [
                'name' => 'Newsletter',
                'description' => 'Generate newsletter content',
                'system_prompt' => 'You are an email marketing specialist creating engaging newsletter content.',
                'user_prompt_template' => 'Create a newsletter section about "{topic}" for {brand_name}.

Brand Context:
- Industry: {industry}
- Brand Voice: {brand_voice}
- Target Audience: {target_audience}

Requirements:
- Personal and conversational tone
- Include valuable insights
- Clear call-to-action
- Length: {content_length}

Create newsletter content that provides value.',
                'default_options' => [
                    'max_tokens' => 1200,
                    'temperature' => 0.7
                ]
            ]
        ];
    }

    /**
     * Generate content
     *
     * @param array $options Content generation options
     * @return array|false Generated content or false on failure
     */
    public function generate_content($options = []) {
        $defaults = [
            'topic' => '',
            'content_type' => 'blog_post',
            'brand_id' => null,
            'custom_prompt' => '',
            'ai_provider' => null,
            'ai_model' => null,
            'auto_publish' => false,
            'post_status' => 'draft',
            'post_category' => null,
            'post_tags' => []
        ];

        $options = wp_parse_args($options, $defaults);

        // Validate required options
        if (empty($options['topic']) && empty($options['custom_prompt'])) {
            $this->logger->error('Topic or custom prompt is required for content generation');
            return false;
        }

        try {
            // Get brand context
            $brand_context = $this->get_brand_context($options['brand_id']);

            // Build prompt with SEO instructions
            $prompt = $this->build_prompt($options, $brand_context);
            if (!$prompt) {
                return false;
            }

            // Generate content using AI
            $ai_options = [
                'model' => $options['ai_model'],
                'system_message' => $prompt['system_prompt']
            ];

            // Merge template options
            if (isset($this->content_templates[$options['content_type']])) {
                $template_options = $this->content_templates[$options['content_type']]['default_options'];
                $ai_options = array_merge($template_options, $ai_options);
            }

            $ai_result = $this->ai_providers->generate_content($prompt['user_prompt'], $ai_options);

            if (!$ai_result) {
                $this->logger->error('AI content generation failed');
                return false;
            }

            // Process generated content
            $processed_content = $this->process_generated_content($ai_result['content'], $options);

            // Create WordPress post if requested
            $post_id = null;
            if ($options['auto_publish']) {
                $post_id = $this->create_wordpress_post($processed_content, $options);
            }

            $result = [
                'content' => $processed_content,
                'ai_result' => $ai_result,
                'post_id' => $post_id,
                'brand_context' => $brand_context,
                'options' => $options
            ];

            $this->logger->info('Content generated successfully', [
                'content_type' => $options['content_type'],
                'topic' => $options['topic'],
                'content_length' => strlen($processed_content['content']),
                'seo_score' => $processed_content['seo_score'] ?? 0,
                'post_id' => $post_id
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Content generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'options' => $options
            ]);
            return false;
        }
    }

    /**
     * Get brand context
     *
     * @param int|null $brand_id Brand ID
     * @return array Brand context
     */
    private function get_brand_context($brand_id = null) {
        if ($brand_id) {
            $brand = $this->brand_manager->get_brand($brand_id);
        } else {
            $brand = $this->brand_manager->get_active_brand();
        }

        if (!$brand) {
            return $this->get_default_brand_context();
        }

        $brand_data = json_decode($brand->brand_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning('Invalid brand data JSON', [
                'brand_id' => $brand->id,
                'error' => json_last_error_msg()
            ]);
            return $this->get_default_brand_context();
        }

        return [
            'brand_name' => $brand_data['name'] ?? $brand->name,
            'industry' => $brand_data['industry'] ?? 'general',
            'target_audience' => $this->format_target_audience($brand_data['target_audience'] ?? []),
            'brand_voice' => $this->format_brand_voice($brand_data['brand_voice'] ?? []),
            'tone' => $brand_data['brand_voice']['tone'] ?? 'professional',
            'key_messages' => implode(', ', $brand_data['key_messages'] ?? []),
            'unique_selling_points' => implode(', ', $brand_data['unique_selling_points'] ?? []),
            'seo_keywords' => implode(', ', $brand_data['seo_keywords'] ?? []),
            'hashtags' => implode(' ', array_map(function($tag) {
                return str_starts_with($tag, '#') ? $tag : '#' . $tag;
            }, $brand_data['social_media']['hashtags'] ?? [])),
            'content_guidelines' => $brand_data['content_guidelines'] ?? []
        ];
    }

    /**
     * Get default brand context
     *
     * @return array Default brand context
     */
    private function get_default_brand_context() {
        return [
            'brand_name' => get_bloginfo('name'),
            'industry' => 'general',
            'target_audience' => 'general audience',
            'brand_voice' => 'professional and informative',
            'tone' => 'professional',
            'key_messages' => 'providing value to our audience',
            'unique_selling_points' => 'quality content and expertise',
            'seo_keywords' => '',
            'hashtags' => '',
            'content_guidelines' => []
        ];
    }

    /**
     * Format target audience for prompt
     *
     * @param array $target_audience Target audience data
     * @return string Formatted target audience
     */
    private function format_target_audience($target_audience) {
        $parts = [];

        if (isset($target_audience['demographics'])) {
            $demo = $target_audience['demographics'];
            if (!empty($demo['age_range'])) {
                $parts[] = "ages {$demo['age_range']}";
            }
            if (!empty($demo['gender']) && $demo['gender'] !== 'all') {
                $parts[] = $demo['gender'];
            }
            if (!empty($demo['income_level']) && $demo['income_level'] !== 'all') {
                $parts[] = "{$demo['income_level']} income";
            }
            if (!empty($demo['education']) && $demo['education'] !== 'all') {
                $parts[] = "{$demo['education']} education";
            }
        }

        if (isset($target_audience['psychographics']['lifestyle'])) {
            $parts[] = $target_audience['psychographics']['lifestyle'];
        }

        return !empty($parts) ? implode(', ', $parts) : 'general audience';
    }

    /**
     * Format brand voice for prompt
     *
     * @param array $brand_voice Brand voice data
     * @return string Formatted brand voice
     */
    private function format_brand_voice($brand_voice) {
        $parts = [];

        if (!empty($brand_voice['tone'])) {
            $parts[] = $brand_voice['tone'];
        }

        if (!empty($brand_voice['personality_traits'])) {
            $parts[] = implode(', ', $brand_voice['personality_traits']);
        }

        if (!empty($brand_voice['communication_style'])) {
            $parts[] = $brand_voice['communication_style'];
        }

        return !empty($parts) ? implode('; ', $parts) : 'professional and informative';
    }

    /**
     * Build prompt for content generation with SEO instructions
     *
     * @param array $options Generation options
     * @param array $brand_context Brand context
     * @return array|false Prompt array or false on failure
     */
    private function build_prompt($options, $brand_context) {
        // Get template
        $template = $this->content_templates[$options['content_type']] ?? null;
        if (!$template) {
            $this->logger->error('Content template not found', [
                'content_type' => $options['content_type']
            ]);
            return false;
        }

        // Build user prompt
        if (!empty($options['custom_prompt'])) {
            $user_prompt = $this->replace_placeholders($options['custom_prompt'], $options, $brand_context);
        } else {
            $user_prompt = $this->replace_placeholders(
                $template['user_prompt_template'],
                $options,
                $brand_context
            );
        }

        // Add SEO instructions based on content type
        $seo_instructions = $this->seo_templates[$options['content_type']] ?? $this->seo_templates['blog_post'];
        $user_prompt .= $seo_instructions;

        return [
            'system_prompt' => $template['system_prompt'],
            'user_prompt' => $user_prompt
        ];
    }

    /**
     * Replace placeholders in prompt template
     *
     * @param string $template Template string
     * @param array $options Generation options
     * @param array $brand_context Brand context
     * @return string Processed template
     */
    private function replace_placeholders($template, $options, $brand_context) {
        $placeholders = array_merge($options, $brand_context);

        // Add additional placeholders
        $placeholders['content_length'] = $this->get_content_length($options['content_type']);
        $placeholders['platform'] = $options['platform'] ?? 'general';
        $placeholders['product_name'] = $options['topic'];
        $placeholders['product_details'] = $options['product_details'] ?? 'Standard product features';

        foreach ($placeholders as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $template = str_replace('{' . $key . '}', $value, $template);
            }
        }

        return $template;
    }

    /**
     * Get content length for content type
     *
     * @param string $content_type Content type
     * @return string Content length description
     */
    private function get_content_length($content_type) {
        $length_map = [
            'blog_post' => '800-1200 words',
            'article' => '1500-2500 words',
            'guide' => '1500-3000 words',
            'review' => '1200-2000 words',
            'product' => '400-800 words',
            'social_media' => '50-280 characters',
            'newsletter' => '300-600 words'
        ];

        return $length_map[$content_type] ?? '500-800 words';
    }

    /**
     * Process generated content
     *
     * @param string $raw_content Raw AI-generated content
     * @param array $options Generation options
     * @return array Processed content
     */
    private function process_generated_content($raw_content, $options) {
        // Extract title if it exists
        $title = $this->extract_title($raw_content, $options);

        // Clean and format content
        $content = $this->clean_content($raw_content);

        // Generate excerpt
        $excerpt = $this->generate_excerpt($content);

        // Generate meta description for SEO
        $meta_description = $this->generate_meta_description($content);

        // Validate SEO structure
        $seo_score = $this->validate_seo_structure($content, $options['content_type']);

        return [
            'title' => $title,
            'content' => $content,
            'excerpt' => $excerpt,
            'meta_description' => $meta_description,
            'content_type' => $options['content_type'],
            'topic' => $options['topic'],
            'seo_score' => $seo_score
        ];
    }

    /**
     * Validate SEO structure and return score
     *
     * @param string $content Content to validate
     * @param string $content_type Content type
     * @return int SEO score (0-100)
     */
    private function validate_seo_structure($content, $content_type) {
        $score = 100;

        // Check H1 count (should be exactly 1)
        $h1_count = preg_match_all('/<h1[^>]*>/i', $content);
        if ($h1_count !== 1) {
            $score -= 20;
            $this->logger->warning("SEO: H1 count is {$h1_count}, should be 1");
        }

        // Check H2 count (should have at least 3)
        $h2_count = preg_match_all('/<h2[^>]*>/i', $content);
        if ($h2_count < 3) {
            $score -= 15;
            $this->logger->warning("SEO: H2 count is {$h2_count}, should be at least 3");
        }

        // Check for tables (except social media)
        if ($content_type !== 'social_media') {
            $table_count = preg_match_all('/<table[^>]*>/i', $content);
            if ($table_count === 0) {
                $score -= 10;
                $this->logger->warning("SEO: No tables found, should have at least 1");
            }
        }

        // Check for lists
        $list_count = preg_match_all('/<[uo]l[^>]*>/i', $content);
        if ($list_count === 0) {
            $score -= 10;
            $this->logger->warning("SEO: No lists found");
        }

        // Check for strong tags (important terms)
        $strong_count = preg_match_all('/<strong[^>]*>/i', $content);
        if ($strong_count < 3) {
            $score -= 5;
        }

        $this->logger->info("SEO validation score: {$score}/100 for content type: {$content_type}");

        return max(0, $score);
    }

    /**
     * Extract title from content
     *
     * @param string $content Content
     * @param array $options Generation options
     * @return string Extracted or generated title
     */
    private function extract_title($content, $options) {
        // Look for HTML heading
        if (preg_match('/<h1[^>]*>(.+?)<\/h1>/i', $content, $matches)) {
            return trim(strip_tags($matches[1]));
        }

        // Look for markdown-style heading
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        // Generate title from topic
        if (!empty($options['topic'])) {
            return ucwords($options['topic']);
        }

        // Extract first sentence as title
        $sentences = preg_split('/[.!?]+/', strip_tags($content), 2);
        if (!empty($sentences[0])) {
            $title = trim($sentences[0]);
            if (strlen($title) > 60) {
                $title = substr($title, 0, 57) . '...';
            }
            return $title;
        }

        return 'Generated Content';
    }

    /**
     * Clean content
     *
     * @param string $content Raw content
     * @return string Cleaned content
     */
    private function clean_content($content) {
        // Remove title if it's at the beginning
        $content = preg_replace('/^#\s+.+\n\n?/m', '', $content);

        // Clean up extra whitespace
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = trim($content);

        // Convert markdown to HTML if needed
        if (strpos($content, '##') !== false || strpos($content, '**') !== false) {
            $content = $this->markdown_to_html($content);
        }

        return $content;
    }

    /**
     * Simple markdown to HTML conversion
     *
     * @param string $markdown Markdown content
     * @return string HTML content
     */
    private function markdown_to_html($markdown) {
        // Headers with IDs
        $markdown = preg_replace_callback('/^### (.+)$/m', function($matches) {
            $id = sanitize_title($matches[1]);
            return '<h3 id="' . $id . '">' . $matches[1] . '</h3>';
        }, $markdown);

        $markdown = preg_replace_callback('/^## (.+)$/m', function($matches) {
            $id = sanitize_title($matches[1]);
            return '<h2 id="' . $id . '">' . $matches[1] . '</h2>';
        }, $markdown);

        $markdown = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $markdown);

        // Bold
        $markdown = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $markdown);

        // Italic
        $markdown = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $markdown);

        // Line breaks
        $markdown = nl2br($markdown);

        return $markdown;
    }

    /**
     * Generate excerpt
     *
     * @param string $content Content
     * @return string Excerpt
     */
    private function generate_excerpt($content) {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);

        if (strlen($text) <= 155) {
            return $text;
        }

        return substr($text, 0, 152) . '...';
    }

    /**
     * Generate meta description
     *
     * @param string $content Content
     * @return string Meta description
     */
    private function generate_meta_description($content) {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);

        if (strlen($text) <= 160) {
            return $text;
        }

        return substr($text, 0, 157) . '...';
    }

    /**
     * Create WordPress post
     *
     * @param array $processed_content Processed content
     * @param array $options Generation options
     * @return int|false Post ID on success, false on failure
     */
    private function create_wordpress_post($processed_content, $options) {
        $post_data = [
            'post_title' => $processed_content['title'],
            'post_content' => $processed_content['content'],
            'post_excerpt' => $processed_content['excerpt'],
            'post_status' => $options['post_status'],
            'post_type' => 'post',
            'post_author' => get_current_user_id(),
            'meta_input' => [
                '_ai_generated' => true,
                '_ai_content_type' => $options['content_type'],
                '_ai_topic' => $options['topic'],
                '_ai_seo_score' => $processed_content['seo_score'],
                '_ai_generation_date' => current_time('mysql')
            ]
        ];

        // Add category if provided
        if (!empty($options['post_category'])) {
            // Support both category ID and category name
            if (is_numeric($options['post_category'])) {
                $post_data['post_category'] = [$options['post_category']];
            } else {
                // Try to find category by name
                $category = get_category_by_slug($options['post_category']);
                if (!$category) {
                    $category = get_term_by('name', $options['post_category'], 'category');
                }
                if ($category) {
                    $post_data['post_category'] = [$category->term_id];
                }
            }
        }

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            $this->logger->error('Failed to create WordPress post', [
                'error' => $post_id->get_error_message()
            ]);
            return false;
        }

        // Add tags
        if (!empty($options['post_tags'])) {
            wp_set_post_tags($post_id, $options['post_tags']);
        }

        // Add meta description for SEO plugins
        if (!empty($processed_content['meta_description'])) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $processed_content['meta_description']);
            update_post_meta($post_id, '_rank_math_description', $processed_content['meta_description']);
        }

        return $post_id;
    }

    /**
     * Get content templates
     *
     * @return array Content templates
     */
    public function get_templates() {
        return $this->content_templates;
    }

    /**
     * Handle generate content AJAX request
     */
    public function handle_generate_content_ajax() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ai_manager_pro_nonce')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('ai_manager_generate_content')) {
            wp_send_json_error('Insufficient permissions');
        }

        try {
            $options = [
                'topic' => sanitize_text_field($_POST['topic'] ?? ''),
                'content_type' => sanitize_text_field($_POST['content_type'] ?? 'blog_post'),
                'brand_id' => intval($_POST['brand_id'] ?? 0) ?: null,
                'custom_prompt' => sanitize_textarea_field($_POST['custom_prompt'] ?? ''),
                'ai_model' => sanitize_text_field($_POST['ai_model'] ?? ''),
                'auto_publish' => (bool) ($_POST['auto_publish'] ?? false),
                'post_status' => sanitize_text_field($_POST['post_status'] ?? 'draft'),
                'post_category' => sanitize_text_field($_POST['post_category'] ?? ''),
                'post_tags' => array_map('sanitize_text_field', $_POST['post_tags'] ?? [])
            ];

            $result = $this->generate_content($options);

            if ($result) {
                wp_send_json_success([
                    'content' => $result['content'],
                    'post_id' => $result['post_id'],
                    'seo_score' => $result['content']['seo_score'],
                    'message' => 'Content generated successfully with SEO score: ' . $result['content']['seo_score'] . '/100'
                ]);
            } else {
                wp_send_json_error('Failed to generate content');
            }

        } catch (\Exception $e) {
            $this->logger->error('Content generation AJAX error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            wp_send_json_error('An error occurred during content generation');
        }
    }
}
