/**
 * Brand Converter JavaScript
 * ממשק להמרת דוגמאות למותגים
 */

class BrandConverter {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.loadAvailableSamples();
  }

  bindEvents() {
    // כפתור יצירת מותג מדוגמה
    jQuery(document).on("click", ".create-from-sample-btn", (e) => {
      e.preventDefault();
      const sampleType = jQuery(e.target).data("sample-type");
      this.openConversionModal(sampleType);
    });

    // כפתור המרה מהירה
    jQuery(document).on("click", ".quick-convert-btn", (e) => {
      e.preventDefault();
      const sampleType = jQuery(e.target).data("sample-type");
      this.quickConvert(sampleType);
    });

    // שמירת מותג מומר
    jQuery(document).on("click", "#save-converted-brand", (e) => {
      e.preventDefault();
      this.saveConvertedBrand();
    });

    // תצוגה מקדימה של דוגמה
    jQuery(document).on("click", ".preview-sample-btn", (e) => {
      e.preventDefault();
      const sampleType = jQuery(e.target).data("sample-type");
      this.previewSample(sampleType);
    });

    // סגירת מודלים
    jQuery(document).on("click", ".modal-close, .modal-overlay", (e) => {
      if (e.target === e.currentTarget) {
        this.closeModal();
      }
    });
  }

  /**
   * פתיחת מודל המרה
   */
  openConversionModal(sampleType) {
    this.showLoading("טוען נתוני דוגמה...");

    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_prepare_brand_form",
        sample_type: sampleType,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        this.hideLoading();
        if (response.success) {
          this.renderConversionModal(
            response.data.form_data,
            response.data.sample_info
          );
        } else {
          this.showNotification("שגיאה: " + response.data, "error");
        }
      },
      error: (xhr, status, error) => {
        this.hideLoading();
        this.showNotification("שגיאה בטעינת הדוגמה: " + error, "error");
      },
    });
  }

  /**
   * רינדור מודל המרה
   */
  renderConversionModal(formData, sampleInfo) {
    const modal = this.createConversionModal(formData, sampleInfo);
    jQuery("body").append(modal);
    jQuery("#conversion-modal").fadeIn(300);

    // מילוי הטופס
    this.populateForm(formData);
  }

  /**
   * יצירת מודל המרה
   */
  createConversionModal(formData, sampleInfo) {
    return `
            <div id="conversion-modal" class="ai-modal" style="display: none;">
                <div class="modal-overlay"></div>
                <div class="modal-content large-modal">
                    <div class="modal-header">
                        <h2>🔄 המרת דוגמה למותג</h2>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="conversion-info">
                            <div class="sample-source">
                                <h3>📋 דוגמה מקורית</h3>
                                <div class="source-details">
                                    <span class="sample-name">${sampleInfo.name}</span>
                                    <span class="sample-industry">${sampleInfo.industry}</span>
                                </div>
                            </div>
                        </div>
                        
                        <form id="brand-conversion-form" class="conversion-form">
                            <input type="hidden" id="sample-type" value="${sampleInfo.type}">
                            
                            <div class="form-section">
                                <h3>🏢 פרטי מותג בסיסיים</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="brand-name">שם המותג *</label>
                                        <input type="text" id="brand-name" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="brand-industry">תעשייה *</label>
                                        <input type="text" id="brand-industry" name="industry" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="brand-description">תיאור המותג *</label>
                                    <textarea id="brand-description" name="description" rows="3" required></textarea>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>🎯 קהל יעד ותקשורת</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="target-audience">קהל יעד</label>
                                        <input type="text" id="target-audience" name="target_audience">
                                    </div>
                                    <div class="form-group">
                                        <label for="brand-tone">טון תקשורת</label>
                                        <select id="brand-tone" name="tone">
                                            <option value="מקצועי">מקצועי</option>
                                            <option value="ידידותי">ידידותי</option>
                                            <option value="סמכותי">סמכותי</option>
                                            <option value="יצירתי">יצירתי</option>
                                            <option value="חם ואישי">חם ואישי</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>🎨 עיצוב ומיתוג</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="brand-colors">צבעי המותג</label>
                                        <div class="colors-input">
                                            <input type="color" id="color-1" name="color_1" value="#667eea">
                                            <input type="color" id="color-2" name="color_2" value="#764ba2">
                                            <button type="button" class="add-color-btn">+ צבע נוסף</button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="brand-website">אתר אינטרנט</label>
                                        <input type="url" id="brand-website" name="website" placeholder="https://example.com">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>💎 ערכי המותג</h3>
                                <div class="values-container">
                                    <div class="values-input">
                                        <input type="text" class="value-input" placeholder="הכנס ערך...">
                                        <button type="button" class="add-value-btn">הוסף</button>
                                    </div>
                                    <div class="values-list" id="values-list"></div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>📱 רשתות חברתיות</h3>
                                <div class="social-media-inputs">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="facebook">Facebook</label>
                                            <input type="text" id="facebook" name="facebook" placeholder="שם המשתמש">
                                        </div>
                                        <div class="form-group">
                                            <label for="instagram">Instagram</label>
                                            <input type="text" id="instagram" name="instagram" placeholder="שם המשתמש">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="twitter">Twitter</label>
                                            <input type="text" id="twitter" name="twitter" placeholder="שם המשתמש">
                                        </div>
                                        <div class="form-group">
                                            <label for="linkedin">LinkedIn</label>
                                            <input type="text" id="linkedin" name="linkedin" placeholder="שם המשתמש">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <div class="modal-actions">
                            <button type="button" class="button button-secondary" onclick="brandConverter.closeModal()">
                                ביטול
                            </button>
                            <button type="button" id="save-converted-brand" class="button button-primary">
                                💾 צור מותג
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
  }

  /**
   * מילוי הטופס בנתוני הדוגמה
   */
  populateForm(formData) {
    // מילוי שדות בסיסיים
    jQuery("#brand-name").val(formData.name || "");
    jQuery("#brand-industry").val(formData.industry || "");
    jQuery("#brand-description").val(formData.description || "");
    jQuery("#target-audience").val(formData.target_audience || "");
    jQuery("#brand-tone").val(formData.tone || "מקצועי");
    jQuery("#brand-website").val(formData.website || "");

    // מילוי צבעים
    if (formData.colors && formData.colors.length > 0) {
      jQuery("#color-1").val(formData.colors[0] || "#667eea");
      if (formData.colors[1]) {
        jQuery("#color-2").val(formData.colors[1]);
      }
    }

    // מילוי ערכים
    if (formData.values && formData.values.length > 0) {
      const valuesList = jQuery("#values-list");
      formData.values.forEach((value) => {
        this.addValueToList(value);
      });
    }

    // מילוי רשתות חברתיות
    if (formData.social_media) {
      jQuery("#facebook").val(formData.social_media.facebook || "");
      jQuery("#instagram").val(formData.social_media.instagram || "");
      jQuery("#twitter").val(formData.social_media.twitter || "");
      jQuery("#linkedin").val(formData.social_media.linkedin || "");
    }

    // הוספת מאזיני אירועים לערכים
    this.bindValueEvents();
  }

  /**
   * המרה מהירה ללא עריכה
   */
  quickConvert(sampleType) {
    const button = jQuery(
      `.quick-convert-btn[data-sample-type="${sampleType}"]`
    );
    const originalText = button.text();

    button.prop("disabled", true).text("ממיר...");

    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_quick_convert_sample",
        sample_type: sampleType,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.showNotification("המותג נוצר בהצלחה!", "success");
          // רענון הדף או עדכון הרשימה
          setTimeout(() => {
            location.reload();
          }, 2000);
        } else {
          this.showNotification("שגיאה: " + response.data, "error");
        }
      },
      error: (xhr, status, error) => {
        this.showNotification("שגיאה בהמרה: " + error, "error");
      },
      complete: () => {
        button.prop("disabled", false).text(originalText);
      },
    });
  }

  /**
   * שמירת מותג מומר
   */
  saveConvertedBrand() {
    const formData = this.collectFormData();

    if (!this.validateForm(formData)) {
      return;
    }

    const button = jQuery("#save-converted-brand");
    const originalText = button.text();

    button.prop("disabled", true).text("שומר...");

    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_convert_sample_to_brand",
        sample_type: jQuery("#sample-type").val(),
        brand_data: JSON.stringify(formData),
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.showNotification("המותג נוצר בהצלחה!", "success");
          this.closeModal();
          // רענון הדף או עדכון הרשימה
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          this.showNotification("שגיאה: " + response.data, "error");
        }
      },
      error: (xhr, status, error) => {
        this.showNotification("שגיאה בשמירה: " + error, "error");
      },
      complete: () => {
        button.prop("disabled", false).text(originalText);
      },
    });
  }

  /**
   * איסוף נתוני הטופס
   */
  collectFormData() {
    const formData = {
      name: jQuery("#brand-name").val(),
      industry: jQuery("#brand-industry").val(),
      description: jQuery("#brand-description").val(),
      target_audience: jQuery("#target-audience").val(),
      tone: jQuery("#brand-tone").val(),
      website: jQuery("#brand-website").val(),
      colors: [jQuery("#color-1").val(), jQuery("#color-2").val()].filter(
        (color) => color
      ),
      values: [],
      social_media: {
        facebook: jQuery("#facebook").val(),
        instagram: jQuery("#instagram").val(),
        twitter: jQuery("#twitter").val(),
        linkedin: jQuery("#linkedin").val(),
      },
    };

    // איסוף ערכים
    jQuery(".value-tag").each(function () {
      const value = jQuery(this).find(".value-text").text();
      if (value) {
        formData.values.push(value);
      }
    });

    return formData;
  }

  /**
   * ולידציה של הטופס
   */
  validateForm(formData) {
    const errors = [];

    if (!formData.name || formData.name.trim().length === 0) {
      errors.push("שם המותג הוא שדה חובה");
    }

    if (!formData.industry || formData.industry.trim().length === 0) {
      errors.push("תעשייה היא שדה חובה");
    }

    if (!formData.description || formData.description.trim().length === 0) {
      errors.push("תיאור המותג הוא שדה חובה");
    }

    if (formData.name && formData.name.length > 100) {
      errors.push("שם המותג ארוך מדי (מקסימום 100 תווים)");
    }

    if (errors.length > 0) {
      this.showNotification("שגיאות בטופס:\n" + errors.join("\n"), "error");
      return false;
    }

    return true;
  }

  /**
   * הוספת ערך לרשימה
   */
  addValueToList(value) {
    if (!value || value.trim().length === 0) return;

    const valuesList = jQuery("#values-list");
    const valueTag = jQuery(`
            <div class="value-tag">
                <span class="value-text">${value}</span>
                <button type="button" class="remove-value-btn">&times;</button>
            </div>
        `);

    valuesList.append(valueTag);
  }

  /**
   * קישור אירועי ערכים
   */
  bindValueEvents() {
    // הוספת ערך
    jQuery(document).on("click", ".add-value-btn", (e) => {
      e.preventDefault();
      const input = jQuery(e.target).siblings(".value-input");
      const value = input.val().trim();

      if (value) {
        this.addValueToList(value);
        input.val("");
      }
    });

    // הסרת ערך
    jQuery(document).on("click", ".remove-value-btn", (e) => {
      e.preventDefault();
      jQuery(e.target).closest(".value-tag").remove();
    });

    // הוספת ערך בלחיצת Enter
    jQuery(document).on("keypress", ".value-input", (e) => {
      if (e.which === 13) {
        e.preventDefault();
        jQuery(e.target).siblings(".add-value-btn").click();
      }
    });
  }

  /**
   * תצוגה מקדימה של דוגמה
   */
  previewSample(sampleType) {
    // כאן נוכל להוסיף תצוגה מקדימה של הדוגמה
    this.showNotification("תצוגה מקדימה - בפיתוח", "info");
  }

  /**
   * טעינת דוגמאות זמינות
   */
  loadAvailableSamples() {
    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_get_available_samples",
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.renderSamplesSection(response.data);
        }
      },
    });
  }

  /**
   * רינדור סקציית הדוגמאות
   */
  renderSamplesSection(samples) {
    const samplesContainer = jQuery("#samples-for-conversion");

    if (samplesContainer.length === 0) return;

    const samplesHtml = samples
      .map(
        (sample) => `
            <div class="sample-conversion-card">
                <div class="sample-icon">${sample.icon}</div>
                <div class="sample-info">
                    <h4>${sample.name}</h4>
                    <p class="sample-industry">${sample.industry}</p>
                    <p class="sample-description">${sample.description}</p>
                </div>
                <div class="sample-actions">
                    <button class="button button-primary create-from-sample-btn" 
                            data-sample-type="${sample.type}">
                        🔄 המר למותג
                    </button>
                    <button class="button button-secondary quick-convert-btn" 
                            data-sample-type="${sample.type}">
                        ⚡ המרה מהירה
                    </button>
                </div>
            </div>
        `
      )
      .join("");

    samplesContainer.html(samplesHtml);
  }

  /**
   * סגירת מודל
   */
  closeModal() {
    jQuery(".ai-modal").fadeOut(300, function () {
      jQuery(this).remove();
    });
  }

  /**
   * הצגת טעינה
   */
  showLoading(message = "טוען...") {
    const loading = jQuery(`
            <div id="loading-overlay" class="ai-loading-overlay">
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <p>${message}</p>
                </div>
            </div>
        `);

    jQuery("body").append(loading);
  }

  /**
   * הסתרת טעינה
   */
  hideLoading() {
    jQuery("#loading-overlay").remove();
  }

  /**
   * הצגת הודעה
   */
  showNotification(message, type = "info") {
    const notification = jQuery(`
            <div class="ai-notification ${type}">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `);

    jQuery("body").append(notification);

    notification.fadeIn(300);

    setTimeout(() => {
      notification.fadeOut(300, function () {
        jQuery(this).remove();
      });
    }, 5000);

    notification.find(".notification-close").on("click", function () {
      notification.fadeOut(300, function () {
        jQuery(this).remove();
      });
    });
  }
}

// אתחול כשהדף נטען
jQuery(document).ready(function () {
  window.brandConverter = new BrandConverter();
});
