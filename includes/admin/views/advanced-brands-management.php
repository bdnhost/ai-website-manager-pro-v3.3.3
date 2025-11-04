<?php
/**
 * Advanced Brands Management Page
 * דף ניהול מותגים מתקדם עם ייבוא/ייצוא JSON
 */

if (!defined('ABSPATH')) {
    exit;
}

// טעינת מנהל המותגים המתקדם
$brand_manager = new AI_Website_Manager_Advanced_Brand_Manager();
$brands = $brand_manager->get_all_brands();
$active_brand = $brand_manager->get_active_brand();
$stats = $brand_manager->get_brands_statistics();
?>

<div class="wrap ai-website-manager-admin">
    <div class="ai-admin-header">
        <h1>🏢 ניהול מותגים מתקדם</h1>
        <p class="description">נהל את המותגים שלך עם תכונות מתקדמות כולל ייבוא וייצוא JSON</p>
    </div>

    <!-- סטטיסטיקות מותגים -->
    <div class="ai-stats-grid">
        <div class="ai-stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3><?php echo $stats['total_brands']; ?></h3>
                <p>סך הכל מותגים</p>
            </div>
        </div>
        <div class="ai-stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3><?php echo $stats['active_brands']; ?></h3>
                <p>מותגים פעילים</p>
            </div>
        </div>
        <div class="ai-stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
                <h3><?php echo $stats['created_this_week']; ?></h3>
                <p>נוצרו השבוע</p>
            </div>
        </div>
        <div class="ai-stat-card">
            <div class="stat-icon">🏭</div>
            <div class="stat-content">
                <h3><?php echo count($stats['by_industry']); ?></h3>
                <p>תעשיות שונות</p>
            </div>
        </div>
    </div>

    <!-- כלי ניהול מהיר -->
    <div class="ai-quick-actions">
        <h2>🚀 פעולות מהירות</h2>
        <div class="quick-actions-grid">
            <button class="ai-btn ai-btn-primary" onclick="openBrandModal()">
                ➕ יצירת מותג חדש
            </button>
            <button class="ai-btn ai-btn-secondary" onclick="openImportModal()">
                📥 ייבוא מותג מ-JSON
            </button>
            <button class="ai-btn ai-btn-secondary" onclick="exportAllBrands()">
                📤 ייצוא כל המותגים
            </button>
            <button class="ai-btn ai-btn-info" id="open-downloads-modal">
                📁 הורד דוגמאות JSON
            </button>
            <button class="ai-btn ai-btn-info" onclick="openTemplateModal()">
                📋 תבנית מותג חדשה
            </button>
        </div>
    </div>

    <!-- דוגמאות להמרה -->
    <div class="ai-samples-section">
        <h2>🔄 המר דוגמאות למותגים</h2>
        <p class="description">התחל מהר עם דוגמאות מוכנות והמר אותן למותגים מותאמים אישית</p>
        <div id="samples-for-conversion" class="samples-conversion-grid">
            <!-- הדוגמאות יטענו כאן באמצעות JavaScript -->
        </div>
    </div>

    <!-- חיפוש וסינון -->
    <div class="ai-search-section">
        <div class="search-controls">
            <input type="text" id="brand-search" placeholder="🔍 חיפוש מותגים..." />
            <select id="industry-filter">
                <option value="">כל התעשיות</option>
                <?php foreach ($stats['by_industry'] as $industry): ?>
                    <option value="<?php echo esc_attr($industry['industry']); ?>">
                        <?php echo esc_html($industry['industry']); ?> (<?php echo $industry['count']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="ai-btn ai-btn-outline" onclick="resetFilters()">איפוס</button>
        </div>
    </div>

    <!-- רשימת מותגים -->
    <div class="ai-brands-grid" id="brands-container">
        <?php foreach ($brands as $brand): ?>
            <div class="ai-brand-card" data-brand-id="<?php echo $brand['id']; ?>"
                data-industry="<?php echo esc_attr($brand['industry']); ?>">
                <div class="brand-header">
                    <div class="brand-info">
                        <h3><?php echo esc_html($brand['name']); ?></h3>
                        <span class="brand-industry"><?php echo esc_html($brand['industry']); ?></span>
                    </div>
                    <div class="brand-status">
                        <?php if ($active_brand && $active_brand['id'] == $brand['id']): ?>
                            <span class="status-badge active">🟢 פעיל</span>
                        <?php else: ?>
                            <span class="status-badge inactive">⚪ לא פעיל</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="brand-description">
                    <p><?php echo esc_html(wp_trim_words($brand['description'], 20)); ?></p>
                </div>

                <div class="brand-meta">
                    <div class="meta-item">
                        <span class="meta-label">🎯 קהל יעד:</span>
                        <span class="meta-value"><?php echo esc_html(wp_trim_words($brand['target_audience'], 5)); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">🗣️ טון דיבור:</span>
                        <span class="meta-value"><?php echo esc_html($brand['brand_voice']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">📅 נוצר:</span>
                        <span class="meta-value"><?php echo date('d/m/Y', strtotime($brand['created_at'])); ?></span>
                    </div>
                </div>

                <div class="brand-keywords"></div> <?php
                $keywords = is_array($brand['keywords']) ? $brand['keywords'] : [];
                foreach (array_slice($keywords, 0, 3) as $keyword):
                    ?>
                    <span class="keyword-tag"><?php echo esc_html($keyword); ?></span>
                <?php endforeach; ?>
                <?php if (count($keywords) > 3): ?>
                    <span class="keyword-more">+<?php echo count($keywords) - 3; ?> נוספות</span>
                <?php endif; ?>
            </div>

            <div class="brand-actions">
                <?php if (!$active_brand || $active_brand['id'] != $brand['id']): ?>
                    <button class="ai-btn ai-btn-sm ai-btn-success" onclick="activateBrand(<?php echo $brand['id']; ?>)">
                        ✅ הפעל
                    </button>
                <?php endif; ?>
                <button class="ai-btn ai-btn-sm ai-btn-primary" onclick="editBrand(<?php echo $brand['id']; ?>)">
                    ✏️ עריכה
                </button>
                <button class="ai-btn ai-btn-sm ai-btn-info" onclick="exportBrand(<?php echo $brand['id']; ?>)">
                    📤 ייצוא
                </button>
                <button class="ai-btn ai-btn-sm ai-btn-secondary" onclick="duplicateBrand(<?php echo $brand['id']; ?>)">
                    📋 שכפול
                </button>
                <button class="ai-btn ai-btn-sm ai-btn-danger" onclick="deleteBrand(<?php echo $brand['id']; ?>)">
                    🗑️ מחיקה
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($brands)): ?>
    <div class="ai-empty-state">
        <div class="empty-icon">🏢</div>
        <h3>אין מותגים עדיין</h3>
        <p>צור את המותג הראשון שלך כדי להתחיל ליצור תוכן מותאם אישית</p>
        <button class="ai-btn ai-btn-primary" onclick="openBrandModal()">
            ➕ יצירת מותג ראשון
        </button>
    </div>
<?php endif; ?>
</div>

<!-- Modal יצירת/עריכת מותג -->
<div id="brand-modal" class="ai-modal" style="display: none;">
    <div class="ai-modal-content">
        <div class="ai-modal-header">
            <h2 id="modal-title">🏢 יצירת מותג חדש</h2>
            <span class="ai-modal-close" onclick="closeBrandModal()">&times;</span>
        </div>
        <div class="ai-modal-body">
            <form id="brand-form">
                <input type="hidden" id="brand-id" name="brand_id" value="">

                <!-- מידע בסיסי -->
                <div class="form-section">
                    <h3>📋 מידע בסיסי</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="brand-name">שם המותג *</label>
                            <input type="text" id="brand-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="brand-industry">תעשייה</label>
                            <select id="brand-industry" name="industry">
                                <option value="">בחר תעשייה</option>
                                <option value="טכנולוגיה">טכנולוגיה</option>
                                <option value="בריאות">בריאות</option>
                                <option value="חינוך">חינוך</option>
                                <option value="מזון">מזון</option>
                                <option value="אופנה">אופנה</option>
                                <option value="נדלן">נדלן</option>
                                <option value="פיננסים">פיננסים</option>
                                <option value="שירותים">שירותים</option>
                                <option value="אחר">אחר</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="brand-description">תיאור המותג</label>
                        <textarea id="brand-description" name="description" rows="3"></textarea>
                    </div>
                </div>

                <!-- קהל יעד וטון -->
                <div class="form-section">
                    <h3>🎯 קהל יעד וטון</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="brand-voice">טון דיבור</label>
                            <select id="brand-voice" name="brand_voice">
                                <option value="professional">מקצועי</option>
                                <option value="friendly">ידידותי</option>
                                <option value="authoritative">סמכותי</option>
                                <option value="casual">רגיל</option>
                                <option value="inspiring">מעורר השראה</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tone-of-voice">סגנון תוכן</label>
                            <select id="tone-of-voice" name="tone_of_voice">
                                <option value="informative">אינפורמטיבי</option>
                                <option value="inspiring">מעורר השראה</option>
                                <option value="conversational">שיחתי</option>
                                <option value="urgent">דחוף</option>
                                <option value="educational">חינוכי</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="target-audience">קהל יעד</label>
                        <textarea id="target-audience" name="target_audience" rows="2"
                            placeholder="תאר את קהל היעד שלך..."></textarea>
                    </div>
                </div>

                <!-- מילות מפתח וערכים -->
                <div class="form-section">
                    <h3>🔑 מילות מפתח וערכים</h3>
                    <div class="form-group">
                        <label for="brand-keywords">מילות מפתח</label>
                        <input type="text" id="brand-keywords" name="keywords"
                            placeholder="הפרד במקף, לדוגמה: טכנולוgia, חדשנות, איכות">
                        <small>הפרד מילות מפתח במקף</small>
                    </div>
                    <div class="form-group">
                        <label for="brand-values">ערכי המותג</label>
                        <input type="text" id="brand-values" name="values"
                            placeholder="הפרד במקף, לדוגמה: אמינות, חדשנות, שירות">
                        <small>הפרד ערכים במקף</small>
                    </div>
                </div>

                <!-- מידע מתקדם -->
                <div class="form-section">
                    <h3>🚀 מידע מתקדם</h3>
                    <div class="form-group">
                        <label for="brand-mission">משימת המותג</label>
                        <textarea id="brand-mission" name="mission" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="brand-vision">חזון המותג</label>
                        <textarea id="brand-vision" name="vision" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="brand-usp">הצעת ערך ייחודית</label>
                        <textarea id="brand-usp" name="unique_selling_proposition" rows="2"></textarea>
                    </div>
                </div>

                <!-- קישורים ומידע ליצירת קשר -->
                <div class="form-section">
                    <h3>🌐 קישורים ומידע ליצירת קשר</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="brand-website">אתר אינטרנט</label>
                            <input type="url" id="brand-website" name="website_url">
                        </div>
                        <div class="form-group">
                            <label for="brand-logo">לוגו (URL)</label>
                            <input type="url" id="brand-logo" name="logo_url">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="ai-modal-footer">
            <button type="button" class="ai-btn ai-btn-secondary" onclick="closeBrandModal()">ביטול</button>
            <button type="button" class="ai-btn ai-btn-primary" onclick="saveBrand()">💾 שמירה</button>
        </div>
    </div>
</div>

<!-- Modal ייבוא JSON -->
<div id="import-modal" class="ai-modal" style="display: none;">
    <div class="ai-modal-content">
        <div class="ai-modal-header">
            <h2>📥 ייבוא מותג מ-JSON</h2>
            <span class="ai-modal-close" onclick="closeImportModal()">&times;</span>
        </div>
        <div class="ai-modal-body">
            <div class="import-options">
                <div class="import-method">
                    <h3>📁 העלאת קובץ JSON</h3>
                    <input type="file" id="json-file" accept=".json" onchange="handleFileUpload(event)">
                    <p class="help-text">בחר קובץ JSON עם נתוני המותג</p>
                </div>

                <div class="import-method">
                    <h3>📝 הדבקת JSON ישירות</h3>
                    <textarea id="json-content" rows="10" placeholder='הדבק כאן את תוכן ה-JSON...'></textarea>
                </div>

                <div class="import-method">
                    <h3>📋 דוגמאות מוכנות</h3>
                    <p class="help-text">בחר דוגמה מוכנה להתחלה מהירה</p>
                    <div class="samples-grid">
                        <button type="button" class="sample-btn" onclick="loadSample('tech_startup')">
                            <span class="sample-icon">💻</span>
                            <span class="sample-name">סטארט-אפ טכנולוגי</span>
                        </button>
                        <button type="button" class="sample-btn" onclick="loadSample('health_wellness')">
                            <span class="sample-icon">🏥</span>
                            <span class="sample-name">בריאות ורווחה</span>
                        </button>
                        <button type="button" class="sample-btn" onclick="loadSample('education')">
                            <span class="sample-icon">🎓</span>
                            <span class="sample-name">חינוך והכשרה</span>
                        </button>
                        <button type="button" class="sample-btn" onclick="loadSample('ecommerce')">
                            <span class="sample-icon">🛒</span>
                            <span class="sample-name">מסחר אלקטרוני</span>
                        </button>
                        <button type="button" class="sample-btn" onclick="loadSample('consulting')">
                            <span class="sample-icon">💼</span>
                            <span class="sample-name">ייעוץ עסקי</span>
                        </button>
                        <button type="button" class="sample-btn" onclick="loadSample('creative')">
                            <span class="sample-icon">🎨</span>
                            <span class="sample-name">יצירה ועיצוב</span>
                        </button>
                        <button type="button" class="sample-btn" onclick="loadSample('restaurant')">
                            <span class="sample-icon">🍝</span>
                            <span class="sample-name">מסעדה</span>
                        </button>
                        <button type="button" class="sample-btn" onclick="loadSample('fitness')">
                            <span class="sample-icon">💪</span>
                            <span class="sample-name">כושר ובריאות</span>
                        </button>
                        <button type="button" class="sample-btn" onclick="loadSample('real_estate')">
                            <span class="sample-icon">🏠</span>
                            <span class="sample-name">נדלן</span>
                        </button>
                        <button type="button" class="sample-btn" onclick="loadSample('finance')">
                            <span class="sample-icon">💰</span>
                            <span class="sample-name">פיננסים</span>
                        </button>
                    </div>
                    <div class="samples-actions">
                        <button type="button" class="ai-btn ai-btn-sm ai-btn-info" onclick="downloadAllSamples()">
                            💾 הורד את כל הדוגמאות
                        </button>
                        <button type="button" class="ai-btn ai-btn-sm ai-btn-secondary" onclick="viewSampleDetails()">
                            👁️ צפה בפרטים
                        </button>
                    </div>
                </div>

                <div class="import-settings">
                    <div class="form-group">
                        <label for="import-brand-name">שם המותג החדש (אופציונלי)</label>
                        <input type="text" id="import-brand-name" placeholder="השאר ריק לשימוש בשם מהקובץ">
                        <small>אם תמלא שדה זה, השם יוחלף בשם החדש</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="ai-modal-footer">
            <button type="button" class="ai-btn ai-btn-secondary" onclick="closeImportModal()">ביטול</button>
            <button type="button" class="ai-btn ai-btn-primary" onclick="importBrand()">📥 ייבוא</button>
        </div>
    </div>
</div>

<!-- Modal תבנית מותג -->
<div id="template-modal" class="ai-modal" style="display: none;">
    <div class="ai-modal-content">
        <div class="ai-modal-header">
            <h2>📋 תבניות מותג</h2>
            <span class="ai-modal-close" onclick="closeTemplateModal()">&times;</span>
        </div>
        <div class="ai-modal-body">
            <div class="template-grid">
                <div class="template-card" onclick="useTemplate('tech_startup')">
                    <div class="template-icon">💻</div>
                    <h3>סטארט-אפ טכנולוגי</h3>
                    <p>מותג לחברת טכנולוגיה חדשנית</p>
                </div>
                <div class="template-card" onclick="useTemplate('health_wellness')">
                    <div class="template-icon">🏥</div>
                    <h3>בריאות ורווחה</h3>
                    <p>מותג לתחום הבריאות והרווחה</p>
                </div>
                <div class="template-card" onclick="useTemplate('education')">
                    <div class="template-icon">🎓</div>
                    <h3>חינוך והכשרה</h3>
                    <p>מותג למוסדות חינוך וקורסים</p>
                </div>
                <div class="template-card" onclick="useTemplate('ecommerce')">
                    <div class="template-icon">🛒</div>
                    <h3>מסחר אלקטרוני</h3>
                    <p>מותג לחנות אונליין</p>
                </div>
                <div class="template-card" onclick="useTemplate('consulting')">
                    <div class="template-icon">💼</div>
                    <h3>ייעוץ עסקי</h3>
                    <p>מותג לחברת ייעוץ מקצועית</p>
                </div>
                <div class="template-card" onclick="useTemplate('creative')">
                    <div class="template-icon">🎨</div>
                    <h3>יצירה ועיצוב</h3>
                    <p>מותג לסטודיו יצירתי</p>
                </div>
            </div>
        </div>
        <div class="ai-modal-footer">
            <button type="button" class="ai-btn ai-btn-secondary" onclick="closeTemplateModal()">ביטול</button>
        </div>
    </div>
</div>

<style>
    .ai-website-manager-admin {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .ai-admin-header {
        text-align: center;
        margin-bottom: 30px;
        padding: 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
    }

    .ai-admin-header h1 {
        margin: 0 0 10px 0;
        font-size: 2.5em;
        font-weight: 700;
    }

    .ai-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .ai-stat-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: transform 0.3s ease;
    }

    .ai-stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        font-size: 2.5em;
        opacity: 0.8;
    }

    .stat-content h3 {
        margin: 0;
        font-size: 2em;
        font-weight: 700;
        color: #333;
    }

    .stat-content p {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 0.9em;
    }

    .ai-quick-actions {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .ai-quick-actions h2 {
        margin: 0 0 20px 0;
        color: #333;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .ai-search-section {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .search-controls {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-controls input,
    .search-controls select {
        padding: 10px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        min-width: 200px;
    }

    .ai-brands-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .ai-brand-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .ai-brand-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        border-color: #667eea;
    }

    .brand-header {
        padding: 20px;
        background: linear-gradient(135deg, #f8f9ff, #e9ecff);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .brand-info h3 {
        margin: 0 0 5px 0;
        font-size: 1.3em;
        font-weight: 700;
        color: #333;
    }

    .brand-industry {
        background: #667eea;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 600;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 600;
    }

    .status-badge.active {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.inactive {
        background: #f8f9fa;
        color: #6c757d;
    }

    .brand-description {
        padding: 0 20px;
        margin: 15px 0;
    }

    .brand-description p {
        margin: 0;
        color: #666;
        line-height: 1.5;
    }

    .brand-meta {
        padding: 0 20px;
        margin: 15px 0;
    }

    .meta-item {
        display: flex;
        margin-bottom: 8px;
        font-size: 0.9em;
    }

    .meta-label {
        font-weight: 600;
        color: #495057;
        min-width: 100px;
    }

    .meta-value {
        color: #6c757d;
    }

    .brand-keywords {
        padding: 0 20px;
        margin: 15px 0;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .keyword-tag {
        background: #e9ecef;
        color: #495057;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.8em;
        font-weight: 500;
    }

    .keyword-more {
        background: #6c757d;
        color: white;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.8em;
        font-weight: 500;
    }

    .brand-actions {
        padding: 20px;
        background: #f8f9fa;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ai-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .ai-btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    .ai-btn-primary {
        background: #667eea;
        color: white;
    }

    .ai-btn-primary:hover {
        background: #5a6fd8;
        transform: translateY(-1px);
    }

    .ai-btn-secondary {
        background: #6c757d;
        color: white;
    }

    .ai-btn-secondary:hover {
        background: #5a6268;
    }

    .ai-btn-success {
        background: #28a745;
        color: white;
    }

    .ai-btn-success:hover {
        background: #218838;
    }

    .ai-btn-info {
        background: #17a2b8;
        color: white;
    }

    .ai-btn-info:hover {
        background: #138496;
    }

    .ai-btn-danger {
        background: #dc3545;
        color: white;
    }

    .ai-btn-danger:hover {
        background: #c82333;
    }

    .ai-btn-outline {
        background: transparent;
        color: #667eea;
        border: 2px solid #667eea;
    }

    .ai-btn-outline:hover {
        background: #667eea;
        color: white;
    }

    .ai-empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .empty-icon {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .ai-empty-state h3 {
        margin: 0 0 10px 0;
        color: #333;
        font-size: 1.5em;
    }

    .ai-empty-state p {
        margin: 0 0 30px 0;
        color: #666;
    }

    /* Modal Styles */
    .ai-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ai-modal-content {
        background: white;
        border-radius: 15px;
        max-width: 800px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .ai-modal-header {
        padding: 25px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border-radius: 15px 15px 0 0;
    }

    .ai-modal-header h2 {
        margin: 0;
        font-size: 1.5em;
    }

    .ai-modal-close {
        font-size: 2em;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }

    .ai-modal-close:hover {
        opacity: 1;
    }

    .ai-modal-body {
        padding: 30px;
    }

    .ai-modal-footer {
        padding: 20px 30px;
        border-top: 1px solid #e9ecef;
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        background: #f8f9fa;
        border-radius: 0 0 15px 15px;
    }

    .form-section {
        margin-bottom: 30px;
        padding: 20px;
        background: #f8f9ff;
        border-radius: 10px;
        border-left: 4px solid #667eea;
    }

    .form-section h3 {
        margin: 0 0 20px 0;
        color: #333;
        font-size: 1.2em;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-group small {
        display: block;
        margin-top: 5px;
        color: #6c757d;
        font-size: 0.85em;
    }

    .help-text {
        color: #6c757d;
        font-size: 0.9em;
        margin-top: 10px;
    }

    .import-options {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .import-method {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px dashed #dee2e6;
    }

    .import-method h3 {
        margin: 0 0 15px 0;
        color: #333;
    }

    .import-settings {
        padding: 20px;
        background: #fff3cd;
        border-radius: 10px;
        border-left: 4px solid #ffc107;
    }

    .template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .template-card {
        padding: 25px;
        background: #f8f9fa;
        border-radius: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .template-card:hover {
        background: #e9ecef;
        border-color: #667eea;
        transform: translateY(-3px);
    }

    .template-icon {
        font-size: 3em;
        margin-bottom: 15px;
    }

    .template-card h3 {
        margin: 0 0 10px 0;
        color: #333;
        font-size: 1.1em;
    }

    .template-card p {
        margin: 0;
        color: #666;
        font-size: 0.9em;
    }

    @media (max-width: 768px) {
        .ai-brands-grid {
            grid-template-columns: 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .search-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .search-controls input,
        .search-controls select {
            min-width: auto;
        }

        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    // JavaScript functions will be added in the next file
</script>