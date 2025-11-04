/**
 * AI Website Manager Pro Enhanced - Admin JavaScript
 * פונקציות JavaScript מתקדמות לממשק הניהול
 */

(function ($) {
  "use strict";

  // אתחול כאשר הדף נטען
  $(document).ready(function () {
    initializeEnhancedFeatures();
  });

  /**
   * אתחול כל התכונות המתקדמות
   */
  function initializeEnhancedFeatures() {
    initializeNotifications();
    initializeTabs();
    initializeAccordions();
    initializeModals();
    initializeTooltips();
    initializeFormValidation();
    initializeLoadingStates();
    initializeProgressBars();
    initializeSearchAndFilter();
    initializeKeyboardShortcuts();
    initializeAutoSave();
  }

  /**
   * מערכת הודעות מתקדמת
   */
  function initializeNotifications() {
    window.showNotification = function (
      message,
      type = "info",
      title = "",
      duration = 5000
    ) {
      const icons = {
        success: "✅",
        error: "❌",
        warning: "⚠️",
        info: "ℹ️",
      };

      const notification = $(`
                <div class="ai-notification ${type}" style="display: none;">
                    <div class="ai-notification-icon">${
                      icons[type] || icons.info
                    }</div>
                    <div class="ai-notification-content">
                        ${
                          title
                            ? `<div class="ai-notification-title">${title}</div>`
                            : ""
                        }
                        <div class="ai-notification-message">${message}</div>
                    </div>
                    <button class="ai-notification-close" aria-label="סגור">&times;</button>
                </div>
            `);

      // הוספת ההודעה לדף
      $("body").append(notification);
      notification.fadeIn(300);

      // סגירה בלחיצה
      notification.find(".ai-notification-close").on("click", function () {
        closeNotification(notification);
      });

      // סגירה אוטומטית
      if (duration > 0) {
        setTimeout(() => closeNotification(notification), duration);
      }

      return notification;
    };

    function closeNotification(notification) {
      notification.fadeOut(300, function () {
        $(this).remove();
      });
    }

    // הודעות קיימות במערכת
    $(".notice").each(function () {
      const $notice = $(this);
      const type = $notice.hasClass("notice-success")
        ? "success"
        : $notice.hasClass("notice-error")
        ? "error"
        : $notice.hasClass("notice-warning")
        ? "warning"
        : "info";

      const message = $notice.find("p").text().trim();
      if (message) {
        showNotification(message, type);
        $notice.hide();
      }
    });
  }

  /**
   * מערכת טאבים
   */
  function initializeTabs() {
    $(".ai-tabs-nav a").on("click", function (e) {
      e.preventDefault();

      const $tab = $(this);
      const target = $tab.attr("href");

      // הסרת active מכל הטאבים
      $tab.closest(".ai-tabs").find(".ai-tabs-nav a").removeClass("active");
      $tab.closest(".ai-tabs").find(".ai-tab-pane").removeClass("active");

      // הפעלת הטאב הנוכחי
      $tab.addClass("active");
      $(target).addClass("active");

      // שמירת הטאב הפעיל ב-localStorage
      localStorage.setItem("activeTab", target);
    });

    // שחזור הטאב הפעיל
    const activeTab = localStorage.getItem("activeTab");
    if (activeTab && $(activeTab).length) {
      $(`.ai-tabs-nav a[href="${activeTab}"]`).click();
    }
  }

  /**
   * מערכת אקורדיון
   */
  function initializeAccordions() {
    $(".ai-accordion-button").on("click", function () {
      const $button = $(this);
      const $content = $button
        .closest(".ai-accordion-item")
        .find(".ai-accordion-content");
      const isExpanded = $button.attr("aria-expanded") === "true";

      // סגירת כל האקורדיונים האחרים
      $button
        .closest(".ai-accordion")
        .find(".ai-accordion-button")
        .attr("aria-expanded", "false");
      $button
        .closest(".ai-accordion")
        .find(".ai-accordion-content")
        .slideUp(300);

      if (!isExpanded) {
        $button.attr("aria-expanded", "true");
        $content.slideDown(300);
      }
    });
  }

  /**
   * מערכת מודלים
   */
  function initializeModals() {
    // פתיחת מודל
    window.openModal = function (modalId) {
      const $modal = $(`#${modalId}`);
      if ($modal.length) {
        $modal.fadeIn(300);
        $("body").addClass("modal-open");

        // מיקוד באלמנט הראשון
        setTimeout(() => {
          $modal.find("input, select, textarea, button").first().focus();
        }, 300);
      }
    };

    // סגירת מודל
    window.closeModal = function (modalId) {
      const $modal = $(`#${modalId}`);
      if ($modal.length) {
        $modal.fadeOut(300);
        $("body").removeClass("modal-open");
      }
    };

    // סגירה בלחיצה על הרקע
    $(document).on("click", ".ai-modal", function (e) {
      if (e.target === this) {
        $(this).fadeOut(300);
        $("body").removeClass("modal-open");
      }
    });

    // סגירה בלחיצה על כפתור הסגירה
    $(document).on("click", ".ai-modal-close", function () {
      $(this).closest(".ai-modal").fadeOut(300);
      $("body").removeClass("modal-open");
    });

    // סגירה ב-ESC
    $(document).on("keydown", function (e) {
      if (e.key === "Escape" && $(".ai-modal:visible").length) {
        $(".ai-modal:visible").fadeOut(300);
        $("body").removeClass("modal-open");
      }
    });
  }

  /**
   * מערכת tooltips
   */
  function initializeTooltips() {
    $("[data-tooltip]").each(function () {
      const $element = $(this);
      const tooltipText = $element.data("tooltip");

      $element.on("mouseenter", function () {
        const tooltip = $(`<div class="ai-tooltip">${tooltipText}</div>`);
        $("body").append(tooltip);

        const rect = this.getBoundingClientRect();
        tooltip
          .css({
            position: "fixed",
            top: rect.top - tooltip.outerHeight() - 8,
            left: rect.left + rect.width / 2 - tooltip.outerWidth() / 2,
            zIndex: 10002,
            background: "#333",
            color: "white",
            padding: "8px 12px",
            borderRadius: "6px",
            fontSize: "12px",
            whiteSpace: "nowrap",
            opacity: 0,
          })
          .animate({ opacity: 1 }, 200);
      });

      $element.on("mouseleave", function () {
        $(".ai-tooltip").fadeOut(200, function () {
          $(this).remove();
        });
      });
    });
  }

  /**
   * ולידציה של טפסים
   */
  function initializeFormValidation() {
    // ולידציה בזמן אמת
    $(".ai-form-control[required]").on("blur", function () {
      validateField($(this));
    });

    $('.ai-form-control[type="email"]').on("blur", function () {
      validateEmail($(this));
    });

    $('.ai-form-control[type="url"]').on("blur", function () {
      validateUrl($(this));
    });

    // ולידציה בשליחת טופס
    $("form").on("submit", function (e) {
      const $form = $(this);
      let isValid = true;

      $form.find(".ai-form-control[required]").each(function () {
        if (!validateField($(this))) {
          isValid = false;
        }
      });

      $form.find('.ai-form-control[type="email"]').each(function () {
        if (!validateEmail($(this))) {
          isValid = false;
        }
      });

      $form.find('.ai-form-control[type="url"]').each(function () {
        if (!validateUrl($(this))) {
          isValid = false;
        }
      });

      if (!isValid) {
        e.preventDefault();
        showNotification("אנא תקן את השגיאות בטופס", "error");

        // מיקוד בשדה הראשון עם שגיאה
        $form.find(".ai-form-control.is-invalid").first().focus();
      }
    });

    function validateField($field) {
      const value = $field.val().trim();
      const isRequired = $field.attr("required");

      if (isRequired && !value) {
        setFieldError($field, "שדה זה הוא חובה");
        return false;
      }

      setFieldSuccess($field);
      return true;
    }

    function validateEmail($field) {
      const value = $field.val().trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (value && !emailRegex.test(value)) {
        setFieldError($field, "כתובת אימייל לא תקינה");
        return false;
      }

      setFieldSuccess($field);
      return true;
    }

    function validateUrl($field) {
      const value = $field.val().trim();

      if (value) {
        try {
          new URL(value);
          setFieldSuccess($field);
          return true;
        } catch {
          setFieldError($field, "כתובת URL לא תקינה");
          return false;
        }
      }

      setFieldSuccess($field);
      return true;
    }

    function setFieldError($field, message) {
      $field.removeClass("is-valid").addClass("is-invalid");

      let $error = $field.siblings(".field-error");
      if (!$error.length) {
        $error = $(
          '<div class="field-error" style="color: #dc3545; font-size: 12px; margin-top: 4px;"></div>'
        );
        $field.after($error);
      }
      $error.text(message);
    }

    function setFieldSuccess($field) {
      $field.removeClass("is-invalid").addClass("is-valid");
      $field.siblings(".field-error").remove();
    }
  }

  /**
   * מצבי טעינה
   */
  function initializeLoadingStates() {
    window.setLoadingState = function (element, loading = true) {
      const $element = $(element);

      if (loading) {
        $element.addClass("ai-loading").prop("disabled", true);

        if ($element.is("button")) {
          const originalText =
            $element.data("original-text") || $element.text();
          $element.data("original-text", originalText);
          $element.html('<span class="ai-spinner"></span> טוען...');
        }
      } else {
        $element.removeClass("ai-loading").prop("disabled", false);

        if ($element.is("button") && $element.data("original-text")) {
          $element.text($element.data("original-text"));
        }
      }
    };

    // טעינה אוטומטית לטפסים
    $("form").on("submit", function () {
      const $submitBtn = $(this).find(
        'button[type="submit"], input[type="submit"]'
      );
      setLoadingState($submitBtn, true);

      // הסרת מצב טעינה אחרי 10 שניות (fallback)
      setTimeout(() => setLoadingState($submitBtn, false), 10000);
    });
  }

  /**
   * פסי התקדמות
   */
  function initializeProgressBars() {
    window.updateProgress = function (selector, percentage) {
      const $progressBar = $(selector).find(".ai-progress-bar");
      $progressBar.css("width", `${Math.min(100, Math.max(0, percentage))}%`);
    };

    // אנימציה של פסי התקדמות בטעינת הדף
    $(".ai-progress-bar").each(function () {
      const $bar = $(this);
      const targetWidth = $bar.data("width") || "0%";

      setTimeout(() => {
        $bar.css("width", targetWidth);
      }, 500);
    });
  }

  /**
   * חיפוש וסינון
   */
  function initializeSearchAndFilter() {
    // חיפוש בזמן אמת
    $("[data-search-target]").on("input", function () {
      const $input = $(this);
      const target = $input.data("search-target");
      const searchTerm = $input.val().toLowerCase();

      $(target).each(function () {
        const $item = $(this);
        const text = $item.text().toLowerCase();
        const matches = text.includes(searchTerm);

        $item.toggle(matches);
      });

      // הצגת הודעה אם אין תוצאות
      const visibleItems = $(target + ":visible").length;
      let $noResults = $(target).parent().find(".no-results");

      if (visibleItems === 0 && searchTerm) {
        if (!$noResults.length) {
          $noResults = $(
            '<div class="no-results" style="text-align: center; padding: 40px; color: #666;">לא נמצאו תוצאות</div>'
          );
          $(target).parent().append($noResults);
        }
        $noResults.show();
      } else {
        $noResults.hide();
      }
    });

    // סינון לפי קטגוריות
    $("[data-filter-target]").on("change", function () {
      const $select = $(this);
      const target = $select.data("filter-target");
      const filterValue = $select.val();

      $(target).each(function () {
        const $item = $(this);
        const itemCategory = $item.data("category");
        const matches = !filterValue || itemCategory === filterValue;

        $item.toggle(matches);
      });
    });
  }

  /**
   * קיצורי מקלדת
   */
  function initializeKeyboardShortcuts() {
    $(document).on("keydown", function (e) {
      // Ctrl/Cmd + S לשמירה
      if ((e.ctrlKey || e.metaKey) && e.key === "s") {
        e.preventDefault();
        const $saveBtn = $('button[type="submit"], .save-button').first();
        if ($saveBtn.length && !$saveBtn.prop("disabled")) {
          $saveBtn.click();
          showNotification("נשמר!", "success", "", 2000);
        }
      }

      // Ctrl/Cmd + N ליצירת חדש
      if ((e.ctrlKey || e.metaKey) && e.key === "n") {
        e.preventDefault();
        const $newBtn = $(".new-button, .add-button").first();
        if ($newBtn.length) {
          $newBtn.click();
        }
      }

      // F1 לעזרה
      if (e.key === "F1") {
        e.preventDefault();
        showNotification(
          "קיצורי מקלדת: Ctrl+S (שמירה), Ctrl+N (חדש), ESC (סגירה)",
          "info",
          "עזרה",
          5000
        );
      }
    });
  }

  /**
   * שמירה אוטומטית
   */
  function initializeAutoSave() {
    let autoSaveTimeout;

    $("form[data-autosave]").each(function () {
      const $form = $(this);
      const interval = parseInt($form.data("autosave")) || 30000; // 30 שניות ברירת מחדל

      $form.find("input, textarea, select").on("input change", function () {
        clearTimeout(autoSaveTimeout);

        autoSaveTimeout = setTimeout(() => {
          saveFormData($form);
        }, interval);
      });
    });

    function saveFormData($form) {
      const formData = $form.serialize();
      const formId = $form.attr("id") || "form_" + Date.now();

      // שמירה ב-localStorage
      localStorage.setItem(`autosave_${formId}`, formData);

      // הצגת אינדיקטור שמירה
      showNotification("נתונים נשמרו אוטומטית", "info", "", 2000);
    }

    // שחזור נתונים בטעינת הדף
    $("form[data-autosave]").each(function () {
      const $form = $(this);
      const formId = $form.attr("id");

      if (formId) {
        const savedData = localStorage.getItem(`autosave_${formId}`);
        if (savedData) {
          // שחזור הנתונים
          const params = new URLSearchParams(savedData);
          params.forEach((value, key) => {
            const $field = $form.find(`[name="${key}"]`);
            if ($field.length) {
              if ($field.is(":checkbox, :radio")) {
                $field.filter(`[value="${value}"]`).prop("checked", true);
              } else {
                $field.val(value);
              }
            }
          });

          showNotification("נתונים שוחזרו מהשמירה האוטומטית", "info", "", 3000);
        }
      }
    });
  }

  /**
   * פונקציות עזר נוספות
   */

  // העתקה ללוח
  window.copyToClipboard = function (text) {
    if (navigator.clipboard) {
      navigator.clipboard
        .writeText(text)
        .then(() => {
          showNotification("הועתק ללוח!", "success", "", 2000);
        })
        .catch(() => {
          fallbackCopyToClipboard(text);
        });
    } else {
      fallbackCopyToClipboard(text);
    }
  };

  function fallbackCopyToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
      document.execCommand("copy");
      showNotification("הועתק ללוח!", "success", "", 2000);
    } catch (err) {
      showNotification("שגיאה בהעתקה", "error", "", 3000);
    }

    document.body.removeChild(textArea);
  }

  // פורמט תאריכים
  window.formatDate = function (date, format = "dd/mm/yyyy") {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, "0");
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, "0");
    const minutes = String(d.getMinutes()).padStart(2, "0");

    return format
      .replace("dd", day)
      .replace("mm", month)
      .replace("yyyy", year)
      .replace("hh", hours)
      .replace("ii", minutes);
  };

  // פורמט מספרים
  window.formatNumber = function (number, decimals = 0) {
    return new Intl.NumberFormat("he-IL", {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    }).format(number);
  };

  // דיבאונס לפונקציות
  window.debounce = function (func, wait, immediate) {
    let timeout;
    return function executedFunction() {
      const context = this;
      const args = arguments;
      const later = function () {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      const callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  };

  // אנימציות חלקות
  window.smoothScrollTo = function (target, duration = 500) {
    const $target = $(target);
    if ($target.length) {
      $("html, body").animate(
        {
          scrollTop: $target.offset().top - 100,
        },
        duration
      );
    }
  };

  // ניהול מצב האפליקציה
  window.AppState = {
    data: {},

    set: function (key, value) {
      this.data[key] = value;
      $(document).trigger("stateChange", [key, value]);
    },

    get: function (key) {
      return this.data[key];
    },

    remove: function (key) {
      delete this.data[key];
      $(document).trigger("stateChange", [key, null]);
    },
  };

  // הוספת סגנונות CSS דינמיים
  const dynamicStyles = `
        <style id="ai-dynamic-styles">
            .modal-open {
                overflow: hidden;
            }
            
            .ai-tooltip {
                pointer-events: none;
                z-index: 10002;
            }
            
            .field-error {
                animation: shake 0.5s ease-in-out;
            }
            
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            
            .ai-loading {
                position: relative;
                pointer-events: none;
            }
            
            .ai-loading::after {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
                animation: loading 1.5s infinite;
            }
            
            .no-results {
                animation: fadeIn 0.3s ease;
            }
        </style>
    `;

  if (!$("#ai-dynamic-styles").length) {
    $("head").append(dynamicStyles);
  }

  // הודעת אתחול
  console.log(
    "🚀 AI Website Manager Pro Enhanced - Admin JavaScript loaded successfully!"
  );
})(jQuery);
