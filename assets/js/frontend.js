/**
 * AI Website Manager Pro - Frontend JavaScript
 *
 * @package AI_Manager_Pro
 * @version 2.0.0
 */

(function ($) {
  "use strict";

  // Global object for AI Manager Pro Frontend
  window.AIManagerProFrontend = {
    /**
     * Initialize frontend functionality
     */
    init: function () {
      this.bindEvents();
      this.initComponents();
    },

    /**
     * Bind event handlers
     */
    bindEvents: function () {
      // Content generation requests from frontend
      $(document).on(
        "submit",
        ".ai-content-request-form",
        this.handleContentRequest
      );

      // API status checks
      $(document).on("click", ".ai-status-check", this.handleStatusCheck);

      // Dynamic content loading
      $(document).on("click", ".ai-load-content", this.handleLoadContent);
    },

    /**
     * Initialize components
     */
    initComponents: function () {
      // Initialize any frontend components
      this.initContentWidgets();
      this.initStatusIndicators();
    },

    /**
     * Handle content generation request
     */
    handleContentRequest: function (e) {
      e.preventDefault();

      const $form = $(this);
      const $submitBtn = $form.find('button[type="submit"]');
      const originalText = $submitBtn.text();

      // Show loading state
      $submitBtn.prop("disabled", true).text("Generating...");

      const formData = {
        action: "ai_manager_pro_frontend_content",
        nonce: aiManagerProFrontend.nonce,
        topic: $form.find('[name="topic"]').val(),
        type: $form.find('[name="type"]').val(),
        length: $form.find('[name="length"]').val(),
      };

      $.post(aiManagerProFrontend.ajaxUrl, formData)
        .done(function (response) {
          if (response.success) {
            AIManagerProFrontend.displayGeneratedContent(response.data.content);
            AIManagerProFrontend.showNotice(
              "Content generated successfully!",
              "success"
            );
          } else {
            AIManagerProFrontend.showNotice(
              "Error: " + (response.data || "Unknown error"),
              "error"
            );
          }
        })
        .fail(function () {
          AIManagerProFrontend.showNotice("Network error occurred", "error");
        })
        .always(function () {
          $submitBtn.prop("disabled", false).text(originalText);
        });
    },

    /**
     * Handle status check
     */
    handleStatusCheck: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const provider = $btn.data("provider");
      const originalText = $btn.text();

      $btn.prop("disabled", true).text("Checking...");

      const data = {
        action: "ai_manager_pro_status_check",
        nonce: aiManagerProFrontend.nonce,
        provider: provider,
      };

      $.post(aiManagerProFrontend.ajaxUrl, data)
        .done(function (response) {
          if (response.success) {
            const status = response.data.status;
            AIManagerProFrontend.updateStatusIndicator(provider, status);
          } else {
            AIManagerProFrontend.showNotice("Status check failed", "error");
          }
        })
        .fail(function () {
          AIManagerProFrontend.showNotice("Network error occurred", "error");
        })
        .always(function () {
          $btn.prop("disabled", false).text(originalText);
        });
    },

    /**
     * Handle dynamic content loading
     */
    handleLoadContent: function (e) {
      e.preventDefault();

      const $btn = $(this);
      const contentType = $btn.data("content-type");
      const targetSelector = $btn.data("target");
      const $target = $(targetSelector);

      if (!$target.length) {
        AIManagerProFrontend.showNotice("Target element not found", "error");
        return;
      }

      $btn.prop("disabled", true);
      $target.html('<div class="ai-loading">Loading content...</div>');

      const data = {
        action: "ai_manager_pro_load_content",
        nonce: aiManagerProFrontend.nonce,
        content_type: contentType,
      };

      $.post(aiManagerProFrontend.ajaxUrl, data)
        .done(function (response) {
          if (response.success) {
            $target.html(response.data.content);
            AIManagerProFrontend.initContentWidgets($target);
          } else {
            $target.html('<div class="ai-error">Failed to load content</div>');
          }
        })
        .fail(function () {
          $target.html('<div class="ai-error">Network error occurred</div>');
        })
        .always(function () {
          $btn.prop("disabled", false);
        });
    },

    /**
     * Display generated content
     */
    displayGeneratedContent: function (content) {
      const $container = $(".ai-generated-content");

      if ($container.length) {
        $container.html(content).show();

        // Scroll to content
        $("html, body").animate(
          {
            scrollTop: $container.offset().top - 50,
          },
          500
        );
      }
    },

    /**
     * Update status indicator
     */
    updateStatusIndicator: function (provider, status) {
      const $indicator = $(
        '.ai-status-indicator[data-provider="' + provider + '"]'
      );

      if ($indicator.length) {
        $indicator
          .removeClass(
            "status-unknown status-online status-offline status-error"
          )
          .addClass("status-" + status);

        const statusText = {
          online: "Online",
          offline: "Offline",
          error: "Error",
          unknown: "Unknown",
        };

        $indicator.find(".status-text").text(statusText[status] || "Unknown");
      }
    },

    /**
     * Initialize content widgets
     */
    initContentWidgets: function ($container) {
      $container = $container || $(document);

      // Initialize any dynamic content widgets
      $container.find(".ai-content-widget").each(function () {
        const $widget = $(this);
        const widgetType = $widget.data("widget-type");

        switch (widgetType) {
          case "auto-refresh":
            AIManagerProFrontend.initAutoRefreshWidget($widget);
            break;
          case "interactive":
            AIManagerProFrontend.initInteractiveWidget($widget);
            break;
        }
      });
    },

    /**
     * Initialize auto-refresh widgets
     */
    initAutoRefreshWidget: function ($widget) {
      const refreshInterval =
        parseInt($widget.data("refresh-interval")) || 30000;
      const contentType = $widget.data("content-type");

      setInterval(function () {
        AIManagerProFrontend.refreshWidgetContent($widget, contentType);
      }, refreshInterval);
    },

    /**
     * Initialize interactive widgets
     */
    initInteractiveWidget: function ($widget) {
      $widget.on("click", ".ai-widget-action", function (e) {
        e.preventDefault();

        const action = $(this).data("action");
        const params = $(this).data("params") || {};

        AIManagerProFrontend.executeWidgetAction($widget, action, params);
      });
    },

    /**
     * Refresh widget content
     */
    refreshWidgetContent: function ($widget, contentType) {
      const data = {
        action: "ai_manager_pro_widget_content",
        nonce: aiManagerProFrontend.nonce,
        content_type: contentType,
        widget_id: $widget.attr("id"),
      };

      $.post(aiManagerProFrontend.ajaxUrl, data)
        .done(function (response) {
          if (response.success) {
            $widget.find(".ai-widget-content").html(response.data.content);
            $widget.trigger("ai:content-updated");
          }
        })
        .fail(function () {
          console.warn("Failed to refresh widget content");
        });
    },

    /**
     * Execute widget action
     */
    executeWidgetAction: function ($widget, action, params) {
      const data = {
        action: "ai_manager_pro_widget_action",
        nonce: aiManagerProFrontend.nonce,
        widget_action: action,
        widget_id: $widget.attr("id"),
        params: params,
      };

      $.post(aiManagerProFrontend.ajaxUrl, data)
        .done(function (response) {
          if (response.success) {
            $widget.trigger("ai:action-completed", [action, response.data]);

            if (response.data.message) {
              AIManagerProFrontend.showNotice(response.data.message, "success");
            }
          } else {
            AIManagerProFrontend.showNotice(
              "Action failed: " + (response.data || "Unknown error"),
              "error"
            );
          }
        })
        .fail(function () {
          AIManagerProFrontend.showNotice("Network error occurred", "error");
        });
    },

    /**
     * Initialize status indicators
     */
    initStatusIndicators: function () {
      $(".ai-status-indicator").each(function () {
        const $indicator = $(this);
        const provider = $indicator.data("provider");
        const autoCheck = $indicator.data("auto-check");

        if (autoCheck) {
          // Check status immediately
          AIManagerProFrontend.checkProviderStatus(provider);

          // Set up periodic checks
          setInterval(function () {
            AIManagerProFrontend.checkProviderStatus(provider);
          }, 60000); // Check every minute
        }
      });
    },

    /**
     * Check provider status
     */
    checkProviderStatus: function (provider) {
      const data = {
        action: "ai_manager_pro_status_check",
        nonce: aiManagerProFrontend.nonce,
        provider: provider,
      };

      $.post(aiManagerProFrontend.ajaxUrl, data)
        .done(function (response) {
          if (response.success) {
            AIManagerProFrontend.updateStatusIndicator(
              provider,
              response.data.status
            );
          }
        })
        .fail(function () {
          AIManagerProFrontend.updateStatusIndicator(provider, "error");
        });
    },

    /**
     * Show notification
     */
    showNotice: function (message, type) {
      type = type || "info";

      // Create notice element
      const $notice = $(
        '<div class="ai-notice ai-notice-' +
          type +
          '">' +
          '<span class="ai-notice-message">' +
          message +
          "</span>" +
          '<button type="button" class="ai-notice-dismiss">&times;</button>' +
          "</div>"
      );

      // Find or create notice container
      let $container = $(".ai-notices");
      if (!$container.length) {
        $container = $('<div class="ai-notices"></div>');
        $("body").prepend($container);
      }

      // Add notice
      $container.append($notice);

      // Handle dismiss
      $notice.find(".ai-notice-dismiss").on("click", function () {
        $notice.fadeOut(300, function () {
          $(this).remove();
        });
      });

      // Auto-dismiss after 5 seconds
      setTimeout(function () {
        $notice.fadeOut(300, function () {
          $(this).remove();
        });
      }, 5000);
    },

    /**
     * Utility functions
     */

    /**
     * Debounce function
     */
    debounce: function (func, wait, immediate) {
      let timeout;
      return function () {
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
    },

    /**
     * Throttle function
     */
    throttle: function (func, limit) {
      let inThrottle;
      return function () {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
          func.apply(context, args);
          inThrottle = true;
          setTimeout(() => (inThrottle = false), limit);
        }
      };
    },

    /**
     * Format date
     */
    formatDate: function (date, format) {
      format = format || "Y-m-d H:i:s";

      const d = new Date(date);
      const pad = (n) => (n < 10 ? "0" + n : n);

      const replacements = {
        Y: d.getFullYear(),
        m: pad(d.getMonth() + 1),
        d: pad(d.getDate()),
        H: pad(d.getHours()),
        i: pad(d.getMinutes()),
        s: pad(d.getSeconds()),
      };

      return format.replace(/[YmdHis]/g, (match) => replacements[match]);
    },

    /**
     * Sanitize HTML
     */
    sanitizeHtml: function (html) {
      const div = document.createElement("div");
      div.textContent = html;
      return div.innerHTML;
    },
  };

  // Initialize when document is ready
  $(document).ready(function () {
    // Only initialize if we have the localized data
    if (typeof aiManagerProFrontend !== "undefined") {
      AIManagerProFrontend.init();
    }
  });
})(jQuery);

// CSS for frontend notices
const css = `
.ai-notices {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 400px;
}

.ai-notice {
    background: #fff;
    border-left: 4px solid #0073aa;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 10px;
    padding: 12px 40px 12px 12px;
    position: relative;
    border-radius: 3px;
}

.ai-notice-success {
    border-left-color: #46b450;
}

.ai-notice-error {
    border-left-color: #dc3232;
}

.ai-notice-warning {
    border-left-color: #ffb900;
}

.ai-notice-dismiss {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    padding: 0;
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
}

.ai-notice-dismiss:hover {
    color: #000;
}

.ai-loading {
    text-align: center;
    padding: 20px;
    color: #666;
}

.ai-error {
    text-align: center;
    padding: 20px;
    color: #dc3232;
}

.ai-status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.ai-status-indicator::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ccc;
}

.ai-status-indicator.status-online::before {
    background: #46b450;
}

.ai-status-indicator.status-offline::before {
    background: #dc3232;
}

.ai-status-indicator.status-error::before {
    background: #ffb900;
}

.ai-content-widget {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.ai-widget-content {
    min-height: 50px;
}
`;

// Inject CSS
const style = document.createElement("style");
style.textContent = css;
document.head.appendChild(style);
