/**
 * Advanced Brands Management JavaScript
 * קוד JavaScript לניהול מותגים מתקדם
 */

jQuery(document).ready(function ($) {
  // אתחול האירועים
  initializeEvents();

  /**
   * אתחול כל האירועים
   */
  function initializeEvents() {
    // חיפוש מותגים
    $("#brand-search").on("input", filterBrands);
    $("#industry-filter").on("change", filterBrands);

    // סגירת modals בלחיצה על הרקע
    $(".ai-modal").on("click", function (e) {
      if (e.target === this) {
        $(this).hide();
      }
    });

    // מניעת סגירה בלחיצה על התוכן
    $(".ai-modal-content").on("click", function (e) {
      e.stopPropagation();
    });
  }

  /**
   * פתיחת modal יצירת/עריכת מותג
   */
  window.openBrandModal = function (brandId = null) {
    if (brandId) {
      // עריכת מותג קיים
      loadBrandData(brandId);
      $("#modal-title").text("✏️ עריכת מותג");
    } else {
      // יצירת מותג חדש
      resetBrandForm();
      $("#modal-title").text("🏢 יצירת מותג חדש");
    }
    $("#brand-modal").show();
  };

  /**
   * סגירת modal מותג
   */
  window.closeBrandModal = function () {
    $("#brand-modal").hide();
    resetBrandForm();
  };

  /**
   * איפוס טופס המותג
   */
  function resetBrandForm() {
    $("#brand-form")[0].reset();
    $("#brand-id").val("");
  }

  /**
   * טעינת נתוני מותג לעריכה
   */
  function loadBrandData(brandId) {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "get_brand_data",
        brand_id: brandId,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          populateBrandForm(response.data);
        } else {
          showNotification("שגיאה בטעינת נתוני המותג", "error");
        }
      },
      error: function () {
        showNotification("שגיאה בחיבור לשרת", "error");
      },
    });
  }

  /**
   * מילוי טופס המותג בנתונים
   */
  function populateBrandForm(brandData) {
    $("#brand-id").val(brandData.id);
    $("#brand-name").val(brandData.name);
    $("#brand-industry").val(brandData.industry);
    $("#brand-description").val(brandData.description);
    $("#brand-voice").val(brandData.brand_voice);
    $("#tone-of-voice").val(brandData.tone_of_voice);
    $("#target-audience").val(brandData.target_audience);
    $("#brand-mission").val(brandData.mission);
    $("#brand-vision").val(brandData.vision);
    $("#brand-usp").val(brandData.unique_selling_proposition);
    $("#brand-website").val(brandData.website_url);
    $("#brand-logo").val(brandData.logo_url);

    // מילות מפתח וערכים (המרה מ-array למחרוזת)
    if (Array.isArray(brandData.keywords)) {
      $("#brand-keywords").val(brandData.keywords.join(", "));
    }
    if (Array.isArray(brandData.values)) {
      $("#brand-values").val(brandData.values.join(", "));
    }
  }

  /**
   * שמירת מותג
   */
  window.saveBrand = function () {
    const formData = new FormData($("#brand-form")[0]);

    // המרת מילות מפתח וערכים למערך
    const keywords = $("#brand-keywords")
      .val()
      .split(",")
      .map((k) => k.trim())
      .filter((k) => k);
    const values = $("#brand-values")
      .val()
      .split(",")
      .map((v) => v.trim())
      .filter((v) => v);

    formData.append("keywords_array", JSON.stringify(keywords));
    formData.append("values_array", JSON.stringify(values));
    formData.append("action", "save_advanced_brand");
    formData.append("nonce", ai_website_manager_ajax.nonce);

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.success) {
          showNotification("המותג נשמר בהצלחה!", "success");
          closeBrandModal();
          location.reload(); // רענון הדף להצגת השינויים
        } else {
          showNotification("שגיאה בשמירת המותג: " + response.data, "error");
        }
      },
      error: function () {
        showNotification("שגיאה בחיבור לשרת", "error");
      },
    });
  };

  /**
   * הפעלת מותג
   */
  window.activateBrand = function (brandId) {
    if (!confirm("האם אתה בטוח שברצונך להפעיל מותג זה?")) {
      return;
    }

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "activate_brand",
        brand_id: brandId,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          showNotification("המותג הופעל בהצלחה!", "success");
          location.reload();
        } else {
          showNotification("שגיאה בהפעלת המותג", "error");
        }
      },
      error: function () {
        showNotification("שגיאה בחיבור לשרת", "error");
      },
    });
  };

  /**
   * עריכת מותג
   */
  window.editBrand = function (brandId) {
    openBrandModal(brandId);
  };

  /**
   * ייצוא מותג
   */
  window.exportBrand = function (brandId) {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "export_brand_json",
        brand_id: brandId,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          downloadJSON(response.data.json, response.data.filename);
          showNotification("המותג יוצא בהצלחה!", "success");
        } else {
          showNotification("שגיאה בייצוא המותג", "error");
        }
      },
      error: function () {
        showNotification("שגיאה בחיבור לשרת", "error");
      },
    });
  };

  /**
   * שכפול מותג
   */
  window.duplicateBrand = function (brandId) {
    const newName = prompt("הכנס שם למותג המשוכפל:");
    if (!newName) return;

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "duplicate_brand",
        brand_id: brandId,
        new_name: newName,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          showNotification("המותג שוכפל בהצלחה!", "success");
          location.reload();
        } else {
          showNotification("שגיאה בשכפול המותג: " + response.data, "error");
        }
      },
      error: function () {
        showNotification("שגיאה בחיבור לשרת", "error");
      },
    });
  };

  /**
   * מחיקת מותג
   */
  window.deleteBrand = function (brandId) {
    if (
      !confirm("האם אתה בטוח שברצונך למחוק מותג זה? פעולה זו לא ניתנת לביטול!")
    ) {
      return;
    }

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "delete_brand",
        brand_id: brandId,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          showNotification("המותג נמחק בהצלחה!", "success");
          location.reload();
        } else {
          showNotification("שגיאה במחיקת המותג", "error");
        }
      },
      error: function () {
        showNotification("שגיאה בחיבור לשרת", "error");
      },
    });
  };

  /**
   * פתיחת modal ייבוא
   */
  window.openImportModal = function () {
    $("#import-modal").show();
  };

  /**
   * סגירת modal ייבוא
   */
  window.closeImportModal = function () {
    $("#import-modal").hide();
    $("#json-file").val("");
    $("#json-content").val("");
    $("#import-brand-name").val("");
  };

  /**
   * טיפול בהעלאת קובץ JSON
   */
  window.handleFileUpload = function (event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.type !== "application/json") {
      showNotification("אנא בחר קובץ JSON תקין", "error");
      return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
      $("#json-content").val(e.target.result);
    };
    reader.readAsText(file);
  };

  /**
   * ייבוא מותג מ-JSON
   */
  window.importBrand = function () {
    const jsonContent = $("#json-content").val().trim();
    const brandName = $("#import-brand-name").val().trim();

    if (!jsonContent) {
      showNotification("אנא הכנס תוכן JSON או בחר קובץ", "error");
      return;
    }

    // בדיקת תקינות JSON
    try {
      JSON.parse(jsonContent);
    } catch (e) {
      showNotification("תוכן JSON לא תקין", "error");
      return;
    }

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "import_brand_json",
        json_data: jsonContent,
        brand_name: brandName,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          showNotification("המותג יובא בהצלחה!", "success");
          closeImportModal();
          location.reload();
        } else {
          showNotification("שגיאה בייבוא המותג: " + response.data, "error");
        }
      },
      error: function () {
        showNotification("שגיאה בחיבור לשרת", "error");
      },
    });
  };

  /**
   * ייצוא כל המותגים
   */
  window.exportAllBrands = function () {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "export_all_brands",
        nonce: ai_website_manager_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          downloadJSON(response.data.json, response.data.filename);
          showNotification("כל המותגים יוצאו בהצלחה!", "success");
        } else {
          showNotification("שגיאה בייצוא המותגים", "error");
        }
      },
      error: function () {
        showNotification("שגיאה בחיבור לשרת", "error");
      },
    });
  };

  /**
   * פתיחת modal תבניות
   */
  window.openTemplateModal = function () {
    $("#template-modal").show();
  };

  /**
   * סגירת modal תבניות
   */
  window.closeTemplateModal = function () {
    $("#template-modal").hide();
  };

  /**
   * שימוש בתבנית מותג
   */
  window.useTemplate = function (templateType) {
    const templates = {
      tech_startup: {
        name: "סטארט-אפ טכנולוגי",
        industry: "טכנולוגיה",
        description: "חברת טכנולוגיה חדשנית המתמחה בפתרונות דיגיטליים מתקדמים",
        brand_voice: "professional",
        tone_of_voice: "inspiring",
        target_audience: "יזמים, מנהלי טכנולוגיה, משקיעים",
        keywords: ["טכנולוגיה", "חדשנות", "דיגיטל", "פתרונות", "עתיד"],
        values: ["חדשנות", "איכות", "מהירות", "שקיפות"],
        mission: "להוביל את המהפכה הטכנולוגיה ולספק פתרונות חדשניים",
        vision: "להיות החברה המובילה בתחום הטכנולוגיה",
        unique_selling_proposition: "פתרונות טכנולוגיים מתקדמים עם שירות אישי",
      },
      health_wellness: {
        name: "בריאות ורווחה",
        industry: "בריאות",
        description: "מותג המתמחה בקידום בריאות ורווחה באמצעות גישה הוליסטית",
        brand_voice: "friendly",
        tone_of_voice: "educational",
        target_audience: "אנשים המעוניינים בבריאות ורווחה, גילאי 25-55",
        keywords: ["בריאות", "רווחה", "תזונה", "כושר", "איזון"],
        values: ["בריאות", "טבעיות", "איזון", "אכפתיות"],
        mission: "לעזור לאנשים להשיג בריאות ורווחה מיטבית",
        vision: "עולם בריא יותר לכולם",
        unique_selling_proposition: "גישה הוליסטית לבריאות עם מעקב אישי",
      },
      education: {
        name: "חינוך והכשרה",
        industry: "חינוך",
        description: "מוסד חינוכי המתמחה בהכשרה מקצועית ופיתוח כישורים",
        brand_voice: "authoritative",
        tone_of_voice: "educational",
        target_audience: "סטודנטים, מקצוענים המעוניינים בהשתלמות",
        keywords: ["חינוך", "הכשרה", "למידה", "כישורים", "קריירה"],
        values: ["מצוינות", "למידה", "התפתחות", "מקצועיות"],
        mission: "לספק חינוך איכותי ולפתח כישורים מקצועיים",
        vision: "להיות המוסד המוביל בהכשרה מקצועית",
        unique_selling_proposition: "הכשרה מעשית עם מעקב אישי וליווי קריירה",
      },
      ecommerce: {
        name: "חנות אונליין",
        industry: "מסחר אלקטרוני",
        description: "חנות אונליין המתמחה במכירת מוצרים איכותיים",
        brand_voice: "friendly",
        tone_of_voice: "conversational",
        target_audience: "קונים אונליין, גילאי 20-50",
        keywords: ["קנייה", "מוצרים", "איכות", "משלוח", "שירות"],
        values: ["איכות", "שירות", "מהירות", "אמינות"],
        mission: "לספק מוצרים איכותיים עם שירות מעולה",
        vision: "להיות החנות האונליין המועדפת",
        unique_selling_proposition:
          "מוצרים איכותיים במחירים הוגנים עם שירות אישי",
      },
      consulting: {
        name: "ייעוץ עסקי",
        industry: "ייעוץ",
        description: "חברת ייעוץ עסקי המתמחה בפתרונות אסטרטגיים",
        brand_voice: "authoritative",
        tone_of_voice: "informative",
        target_audience: "בעלי עסקים, מנהלים בכירים",
        keywords: ["ייעוץ", "אסטרטגיה", "עסקים", "פתרונות", "צמיחה"],
        values: ["מקצועיות", "תוצאות", "שקיפות", "מצוינות"],
        mission: "לעזור לעסקים להשיג את המטרות שלהם",
        vision: "להיות שותף האסטרטגי המובחר",
        unique_selling_proposition: "ייעוץ מותאם אישית עם התמקדות בתוצאות",
      },
      creative: {
        name: "סטודיו יצירתי",
        industry: "יצירה ועיצוב",
        description: "סטודיו יצירתי המתמחה בעיצוב ופתרונות ויזואליים",
        brand_voice: "casual",
        tone_of_voice: "inspiring",
        target_audience: "עסקים המחפשים פתרונות עיצוב, אמנים",
        keywords: ["עיצוב", "יצירתיות", "אמנות", "ויזואל", "חדשנות"],
        values: ["יצירתיות", "חדשנות", "איכות", "ייחודיות"],
        mission: "להביא יצירתיות לעולם העסקי",
        vision: "להיות הסטודיו היצירתי המוביל",
        unique_selling_proposition: "עיצובים ייחודיים שמספרים סיפור",
      },
    };

    const template = templates[templateType];
    if (template) {
      closeTemplateModal();
      populateBrandForm(template);
      openBrandModal();
    }
  };

  /**
   * סינון מותגים
   */
  function filterBrands() {
    const searchTerm = $("#brand-search").val().toLowerCase();
    const selectedIndustry = $("#industry-filter").val();

    $(".ai-brand-card").each(function () {
      const $card = $(this);
      const brandName = $card.find("h3").text().toLowerCase();
      const brandDescription = $card
        .find(".brand-description p")
        .text()
        .toLowerCase();
      const brandIndustry = $card.data("industry");

      let showCard = true;

      // סינון לפי חיפוש
      if (
        searchTerm &&
        !brandName.includes(searchTerm) &&
        !brandDescription.includes(searchTerm)
      ) {
        showCard = false;
      }

      // סינון לפי תעשייה
      if (selectedIndustry && brandIndustry !== selectedIndustry) {
        showCard = false;
      }

      $card.toggle(showCard);
    });
  }

  /**
   * איפוס מסננים
   */
  window.resetFilters = function () {
    $("#brand-search").val("");
    $("#industry-filter").val("");
    $(".ai-brand-card").show();
  };

  /**
   * הורדת קובץ JSON
   */
  function downloadJSON(jsonData, filename) {
    const blob = new Blob([jsonData], { type: "application/json" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  /**
   * הצגת הודעה
   */
  function showNotification(message, type = "info") {
    // יצירת הודעה
    const notification = $(`
            <div class="ai-notification ai-notification-${type}">
                <span class="notification-icon">
                    ${
                      type === "success" ? "✅" : type === "error" ? "❌" : "ℹ️"
                    }
                </span>
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `);

    // הוספת סגנונות אם לא קיימים
    if (!$("#ai-notification-styles").length) {
      $("head").append(`
                <style id="ai-notification-styles">
                .ai-notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: white;
                    padding: 15px 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    z-index: 10001;
                    min-width: 300px;
                    animation: slideIn 0.3s ease;
                }
                .ai-notification-success { border-left: 4px solid #28a745; }
                .ai-notification-error { border-left: 4px solid #dc3545; }
                .ai-notification-info { border-left: 4px solid #17a2b8; }
                .notification-icon { font-size: 1.2em; }
                .notification-message { flex: 1; }
                .notification-close {
                    background: none;
                    border: none;
                    font-size: 1.5em;
                    cursor: pointer;
                    opacity: 0.5;
                }
                .notification-close:hover { opacity: 1; }
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                </style>
            `);
    }

    // הוספת ההודעה לדף
    $("body").append(notification);

    // סגירה בלחיצה
    notification.find(".notification-close").on("click", function () {
      notification.fadeOut(300, function () {
        $(this).remove();
      });
    });

    // סגירה אוטומטית אחרי 5 שניות
    setTimeout(function () {
      notification.fadeOut(300, function () {
        $(this).remove();
      });
    }, 5000);
  }
});
/**
 * טעינת דוגמת מותג
 */
window.loadSample = function (sampleType) {
  $.ajax({
    url: ajaxurl,
    type: "POST",
    data: {
      action: "get_brand_sample",
      sample_type: sampleType,
      nonce: ai_website_manager_ajax.nonce,
    },
    success: function (response) {
      if (response.success) {
        $("#json-content").val(JSON.stringify(response.data, null, 2));
        showNotification(`דוגמת "${sampleType}" נטענה בהצלחה!`, "success");
      } else {
        showNotification("שגיאה בטעינת הדוגמה", "error");
      }
    },
    error: function () {
      showNotification("שגיאה בחיבור לשרת", "error");
    },
  });
};

/**
 * הורדת כל הדוגמאות
 */
window.downloadAllSamples = function () {
  $.ajax({
    url: ajaxurl,
    type: "POST",
    data: {
      action: "download_all_brand_samples",
      nonce: ai_website_manager_ajax.nonce,
    },
    success: function (response) {
      if (response.success) {
        downloadJSON(response.data.json, response.data.filename);
        showNotification("כל הדוגמאות הורדו בהצלחה!", "success");
      } else {
        showNotification("שגיאה בהורדת הדוגמאות", "error");
      }
    },
    error: function () {
      showNotification("שגיאה בחיבור לשרת", "error");
    },
  });
};

/**
 * צפייה בפרטי דוגמה
 */
window.viewSampleDetails = function () {
  const modalHtml = `
            <div id="samples-details-modal" class="ai-modal">
                <div class="ai-modal-content">
                    <div class="ai-modal-header">
                        <h2>📋 פרטי דוגמאות המותגים</h2>
                        <span class="ai-modal-close" onclick="closeSamplesDetailsModal()">&times;</span>
                    </div>
                    <div class="ai-modal-body">
                        <div class="samples-info">
                            <h3>🎯 מה כלול בכל דוגמה:</h3>
                            <ul>
                                <li><strong>מידע בסיסי:</strong> שם, תיאור, תעשייה</li>
                                <li><strong>קהל יעד:</strong> דמוגרפיה, פסיכוגרפיה, כאבים ומטרות</li>
                                <li><strong>זהות המותג:</strong> טון דיבור, ערכים, משימה וחזון</li>
                                <li><strong>תוכן שיווקי:</strong> מילות מפתח, עמודי תוכן, USP</li>
                                <li><strong>עיצוב:</strong> צבעים, פונטים, לוגו</li>
                                <li><strong>רשתות חברתיות:</strong> קישורים לפלטפורמות</li>
                                <li><strong>SEO:</strong> מילות מפתח, תבניות מטא</li>
                            </ul>
                            
                            <h3>🚀 איך להשתמש:</h3>
                            <ol>
                                <li>בחר דוגמה המתאימה לתחום שלך</li>
                                <li>הדוגמה תיטען אוטומטית לשדה JSON</li>
                                <li>ערוך את הפרטים בהתאם לעסק שלך</li>
                                <li>לחץ "ייבוא" ליצירת המותג</li>
                            </ol>
                        </div>
                    </div>
                    <div class="ai-modal-footer">
                        <button type="button" class="ai-btn ai-btn-secondary" onclick="closeSamplesDetailsModal()">סגור</button>
                    </div>
                </div>
            </div>
        `;

  $("body").append(modalHtml);
  $("#samples-details-modal").fadeIn(300);
};

/**
 * סגירת modal פרטי דוגמאות
 */
window.closeSamplesDetailsModal = function () {
  $("#samples-details-modal").fadeOut(300, function () {
    $(this).remove();
  });
};
