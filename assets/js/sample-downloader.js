/**
 * Sample Downloader JavaScript
 * ממשק להורדת דוגמאות JSON
 */

class SampleDownloader {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.loadAvailableDownloads();
  }

  bindEvents() {
    // כפתור הורדת דוגמה בודדת
    jQuery(document).on("click", ".download-sample-btn", (e) => {
      e.preventDefault();
      const sampleType = jQuery(e.target).data("sample-type");
      this.downloadSample(sampleType);
    });

    // כפתור הורדת כל הדוגמאות
    jQuery(document).on("click", "#download-all-samples", (e) => {
      e.preventDefault();
      this.downloadAllSamples();
    });

    // כפתור ייצוא מותג
    jQuery(document).on("click", ".export-brand-btn", (e) => {
      e.preventDefault();
      const brandId = jQuery(e.target).data("brand-id");
      this.exportBrand(brandId);
    });

    // פתיחת מודל הורדות
    jQuery(document).on("click", "#open-downloads-modal", (e) => {
      e.preventDefault();
      this.openDownloadsModal();
    });

    // סגירת מודל
    jQuery(document).on("click", ".modal-close, .modal-overlay", (e) => {
      if (e.target === e.currentTarget) {
        this.closeModal();
      }
    });
  }

  /**
   * הורדת דוגמה בודדת
   */
  downloadSample(sampleType) {
    if (!sampleType) {
      this.showNotification("שגיאה: סוג דוגמה לא צוין", "error");
      return;
    }

    const button = jQuery(
      `.download-sample-btn[data-sample-type="${sampleType}"]`
    );
    const originalText = button.text();

    // הצגת מצב טעינה
    button.prop("disabled", true).text("מוריד...");

    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_download_sample",
        sample_type: sampleType,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.triggerDownload(
            response.data.download_url,
            response.data.filename
          );
          this.showNotification("הדוגמה הורדה בהצלחה!", "success");
          this.updateDownloadStats();
        } else {
          this.showNotification("שגיאה: " + response.data, "error");
        }
      },
      error: (xhr, status, error) => {
        this.showNotification("שגיאה בהורדה: " + error, "error");
      },
      complete: () => {
        button.prop("disabled", false).text(originalText);
      },
    });
  }

  /**
   * הורדת כל הדוגמאות
   */
  downloadAllSamples() {
    const button = jQuery("#download-all-samples");
    const originalText = button.text();

    // הצגת מצב טעינה
    button.prop("disabled", true).text("יוצר ZIP...");

    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_download_all_samples",
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.triggerDownload(
            response.data.download_url,
            response.data.filename
          );
          this.showNotification(
            `${response.data.files_count} דוגמאות הורדו בהצלחה!`,
            "success"
          );
          this.updateDownloadStats();
        } else {
          this.showNotification("שגיאה: " + response.data, "error");
        }
      },
      error: (xhr, status, error) => {
        this.showNotification("שגיאה בהורדה: " + error, "error");
      },
      complete: () => {
        button.prop("disabled", false).text(originalText);
      },
    });
  }

  /**
   * ייצוא מותג
   */
  exportBrand(brandId) {
    if (!brandId) {
      this.showNotification("שגיאה: מזהה מותג לא צוין", "error");
      return;
    }

    const button = jQuery(`.export-brand-btn[data-brand-id="${brandId}"]`);
    const originalText = button.text();

    // הצגת מצב טעינה
    button.prop("disabled", true).text("מייצא...");

    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_export_brand",
        brand_id: brandId,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.triggerDownload(
            response.data.download_url,
            response.data.filename
          );
          this.showNotification("המותג יוצא בהצלחה!", "success");
          this.updateDownloadStats();
        } else {
          this.showNotification("שגיאה: " + response.data, "error");
        }
      },
      error: (xhr, status, error) => {
        this.showNotification("שגיאה בייצוא: " + error, "error");
      },
      complete: () => {
        button.prop("disabled", false).text(originalText);
      },
    });
  }

  /**
   * הפעלת הורדה
   */
  triggerDownload(url, filename) {
    // יצירת קישור זמני להורדה
    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    link.style.display = "none";

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  /**
   * פתיחת מודל הורדות
   */
  openDownloadsModal() {
    const modal = this.createDownloadsModal();
    jQuery("body").append(modal);
    jQuery("#downloads-modal").fadeIn(300);
    this.loadAvailableDownloads();
  }

  /**
   * יצירת מודל הורדות
   */
  createDownloadsModal() {
    return `
            <div id="downloads-modal" class="ai-modal" style="display: none;">
                <div class="modal-overlay"></div>
                <div class="modal-content large-modal">
                    <div class="modal-header">
                        <h2>🗂️ הורדת דוגמאות JSON</h2>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="downloads-tabs">
                            <button class="tab-button active" data-tab="samples">דוגמאות מוכנות</button>
                            <button class="tab-button" data-tab="history">היסטוריית הורדות</button>
                        </div>
                        
                        <div id="samples-tab" class="tab-content active">
                            <div class="samples-grid">
                                ${this.renderSamplesGrid()}
                            </div>
                            <div class="bulk-actions">
                                <button id="download-all-samples" class="button button-primary">
                                    📦 הורד הכל כ-ZIP
                                </button>
                            </div>
                        </div>
                        
                        <div id="history-tab" class="tab-content">
                            <div id="download-history">
                                <div class="loading">טוען היסטוריה...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
  }

  /**
   * רינדור רשת הדוגמאות
   */
  renderSamplesGrid() {
    const samples = this.getSamplesList();

    return samples
      .map(
        (sample) => `
            <div class="sample-card">
                <div class="sample-icon">${sample.icon}</div>
                <div class="sample-info">
                    <h3>${sample.name}</h3>
                    <p>${sample.description}</p>
                    <div class="sample-meta">
                        <span class="industry">${sample.industry}</span>
                        <span class="audience">${sample.audience}</span>
                    </div>
                </div>
                <div class="sample-actions">
                    <button class="button button-primary download-sample-btn" 
                            data-sample-type="${sample.type}">
                        💾 הורד JSON
                    </button>
                    <button class="button button-secondary preview-sample-btn" 
                            data-sample-type="${sample.type}">
                        👁️ תצוגה מקדימה
                    </button>
                </div>
            </div>
        `
      )
      .join("");
  }

  /**
   * קבלת רשימת דוגמאות
   */
  getSamplesList() {
    return [
      {
        type: "tech_startup",
        name: "TechFlow Solutions",
        description: "חברת סטארט-אפ טכנולוגית מתקדמת",
        industry: "טכנולוגיה",
        audience: "מפתחים",
        icon: "💻",
      },
      {
        type: "wellness",
        name: "HealthyLife Wellness",
        description: "מרכז בריאות ורווחה מקצועי",
        industry: "בריאות",
        audience: "אנשים בריאים",
        icon: "🏥",
      },
      {
        type: "education",
        name: "EduTech Academy",
        description: "מוסד חינוכי דיגיטלי",
        industry: "חינוך",
        audience: "סטודנטים",
        icon: "🎓",
      },
      {
        type: "ecommerce",
        name: "StyleHub Store",
        description: "חנות אופנה אונליין",
        industry: "מסחר",
        audience: "קונים",
        icon: "🛒",
      },
      {
        type: "consulting",
        name: "Business Growth Partners",
        description: "חברת ייעוץ עסקי",
        industry: "ייעוץ",
        audience: "עסקים",
        icon: "💼",
      },
    ];
  }

  /**
   * טעינת הורדות זמינות
   */
  loadAvailableDownloads() {
    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_get_available_downloads",
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.renderDownloadHistory(response.data);
        }
      },
    });
  }

  /**
   * רינדור היסטוריית הורדות
   */
  renderDownloadHistory(downloads) {
    const historyContainer = jQuery("#download-history");

    if (downloads.length === 0) {
      historyContainer.html(
        '<div class="no-downloads">אין הורדות קודמות</div>'
      );
      return;
    }

    const historyHtml = downloads
      .map(
        (download) => `
            <div class="download-item">
                <div class="download-icon">
                    ${download.type === "zip" ? "📦" : "📄"}
                </div>
                <div class="download-info">
                    <div class="filename">${download.filename}</div>
                    <div class="download-meta">
                        <span class="size">${this.formatFileSize(download.size)}</span>
                        <span class="date">${this.formatDate(download.created)}</span>
                    </div>
                </div>
                <div class="download-actions">
                    <a href="${download.download_url}" class="button button-small">
                        ⬇️ הורד שוב
                    </a>
                </div>
            </div>
        `
      )
      .join("");

    historyContainer.html(historyHtml);
  }

  /**
   * עדכון סטטיסטיקות הורדה
   */
  updateDownloadStats() {
    const statsElement = jQuery(".download-stats");
    if (statsElement.length) {
      // עדכון מונה הורדות
      const currentCount =
        parseInt(statsElement.find(".downloads-count").text()) || 0;
      statsElement.find(".downloads-count").text(currentCount + 1);
    }
  }

  /**
   * סגירת מודל
   */
  closeModal() {
    jQuery("#downloads-modal").fadeOut(300, function () {
      jQuery(this).remove();
    });
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

    // הסרה אוטומטית אחרי 5 שניות
    setTimeout(() => {
      notification.fadeOut(300, function () {
        jQuery(this).remove();
      });
    }, 5000);

    // הסרה בלחיצה
    notification.find(".notification-close").on("click", function () {
      notification.fadeOut(300, function () {
        jQuery(this).remove();
      });
    });
  }

  /**
   * פורמט גודל קובץ
   */
  formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  }

  /**
   * פורמט תאריך
   */
  formatDate(timestamp) {
    const date = new Date(timestamp * 1000);
    return (
      date.toLocaleDateString("he-IL") + " " + date.toLocaleTimeString("he-IL")
    );
  }
}

// אתחול כשהדף נטען
jQuery(document).ready(function () {
  window.sampleDownloader = new SampleDownloader();

  // טיפול בטאבים
  jQuery(document).on("click", ".tab-button", function () {
    const tabName = jQuery(this).data("tab");

    // עדכון כפתורי הטאבים
    jQuery(".tab-button").removeClass("active");
    jQuery(this).addClass("active");

    // עדכון תוכן הטאבים
    jQuery(".tab-content").removeClass("active");
    jQuery(`#${tabName}-tab`).addClass("active");
  });
});
