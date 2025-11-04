/**
 * AI Website Manager Pro - Advanced JSON Editor
 *
 * @package AI_Manager_Pro
 * @version 3.2.0
 */

(function ($) {
  "use strict";

  // Global object for Advanced JSON Editor
  window.AIManagerProJSONEditor = {
    editors: {},

    /**
     * Initialize JSON Editor
     */
    init: function () {
      this.loadCodeMirror();
      this.bindEvents();
      this.initExistingEditors();
    },

    /**
     * Load CodeMirror library
     */
    loadCodeMirror: function () {
      if (typeof CodeMirror === "undefined") {
        // Load CodeMirror CSS
        $("<link>")
          .attr("rel", "stylesheet")
          .attr(
            "href",
            "https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css"
          )
          .appendTo("head");

        $("<link>")
          .attr("rel", "stylesheet")
          .attr(
            "href",
            "https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css"
          )
          .appendTo("head");

        // Load CodeMirror JS
        $.getScript(
          "https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"
        )
          .then(() => {
            return $.getScript(
              "https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"
            );
          })
          .then(() => {
            return $.getScript(
              "https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closebrackets.min.js"
            );
          })
          .then(() => {
            return $.getScript(
              "https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/matchbrackets.min.js"
            );
          })
          .then(() => {
            return $.getScript(
              "https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/lint/lint.min.js"
            );
          })
          .then(() => {
            return $.getScript(
              "https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/lint/json-lint.min.js"
            );
          })
          .then(() => {
            this.initExistingEditors();
          });
      }
    },

    /**
     * Bind event handlers
     */
    bindEvents: function () {
      // Initialize editor on textarea focus
      $(document).on(
        "focus",
        ".json-editor-textarea",
        this.initEditor.bind(this)
      );

      // Format JSON button
      $(document).on("click", ".format-json-btn", this.formatJSON.bind(this));

      // Validate JSON button
      $(document).on(
        "click",
        ".validate-json-btn",
        this.validateJSON.bind(this)
      );

      // Full screen toggle
      $(document).on(
        "click",
        ".fullscreen-json-btn",
        this.toggleFullscreen.bind(this)
      );
    },

    /**
     * Initialize existing editors
     */
    initExistingEditors: function () {
      $(".json-editor-textarea").each((index, element) => {
        if (!$(element).hasClass("editor-initialized")) {
          this.initEditor({ target: element });
        }
      });
    },

    /**
     * Initialize CodeMirror editor
     */
    initEditor: function (event) {
      const textarea = event.target;
      const $textarea = $(textarea);

      if (
        $textarea.hasClass("editor-initialized") ||
        typeof CodeMirror === "undefined"
      ) {
        return;
      }

      const editorId = $textarea.attr("id") || "editor-" + Date.now();
      $textarea.attr("id", editorId);

      // Create editor container
      const $container = $('<div class="json-editor-container"></div>');
      const $toolbar = this.createToolbar(editorId);

      $textarea.wrap($container);
      $textarea.before($toolbar);

      // Initialize CodeMirror
      const editor = CodeMirror.fromTextArea(textarea, {
        mode: { name: "javascript", json: true },
        theme: "monokai",
        lineNumbers: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        indentUnit: 2,
        tabSize: 2,
        lineWrapping: true,
        foldGutter: true,
        gutters: [
          "CodeMirror-linenumbers",
          "CodeMirror-foldgutter",
          "CodeMirror-lint-markers",
        ],
        lint: true,
        extraKeys: {
          "Ctrl-Space": "autocomplete",
          F11: () => this.toggleFullscreen(null, editorId),
          Esc: () => this.exitFullscreen(editorId),
        },
      });

      // Store editor reference
      this.editors[editorId] = editor;
      $textarea.addClass("editor-initialized");

      // Auto-format on load if valid JSON
      try {
        const value = editor.getValue();
        if (value.trim()) {
          const parsed = JSON.parse(value);
          editor.setValue(JSON.stringify(parsed, null, 2));
        }
      } catch (e) {
        // Invalid JSON, leave as is
      }

      // Real-time validation
      editor.on("change", () => {
        this.validateJSONRealtime(editorId);
      });

      return editor;
    },

    /**
     * Create editor toolbar
     */
    createToolbar: function (editorId) {
      return $(`
        <div class="json-editor-toolbar">
          <button type="button" class="button format-json-btn" data-editor="${editorId}">
            <span class="dashicons dashicons-editor-code"></span>
            Format JSON
          </button>
          <button type="button" class="button validate-json-btn" data-editor="${editorId}">
            <span class="dashicons dashicons-yes-alt"></span>
            Validate
          </button>
          <button type="button" class="button fullscreen-json-btn" data-editor="${editorId}">
            <span class="dashicons dashicons-fullscreen-alt"></span>
            Full Screen
          </button>
          <div class="json-status" id="json-status-${editorId}">
            <span class="status-indicator"></span>
            <span class="status-text">Ready</span>
          </div>
        </div>
      `);
    },

    /**
     * Format JSON
     */
    formatJSON: function (event) {
      const editorId = $(event.target)
        .closest(".format-json-btn")
        .data("editor");
      const editor = this.editors[editorId];

      if (!editor) return;

      try {
        const value = editor.getValue();
        const parsed = JSON.parse(value);
        const formatted = JSON.stringify(parsed, null, 2);
        editor.setValue(formatted);
        this.showStatus(editorId, "success", "JSON formatted successfully");
      } catch (e) {
        this.showStatus(editorId, "error", "Invalid JSON: " + e.message);
      }
    },

    /**
     * Validate JSON
     */
    validateJSON: function (event) {
      const editorId = $(event.target)
        .closest(".validate-json-btn")
        .data("editor");
      this.validateJSONRealtime(editorId, true);
    },

    /**
     * Real-time JSON validation
     */
    validateJSONRealtime: function (editorId, showSuccess = false) {
      const editor = this.editors[editorId];
      if (!editor) return;

      try {
        const value = editor.getValue().trim();
        if (!value) {
          this.showStatus(editorId, "neutral", "Empty");
          return;
        }

        JSON.parse(value);
        if (showSuccess) {
          this.showStatus(editorId, "success", "Valid JSON");
        } else {
          this.showStatus(editorId, "neutral", "Valid");
        }
      } catch (e) {
        this.showStatus(editorId, "error", "Invalid JSON");
      }
    },

    /**
     * Toggle fullscreen mode
     */
    toggleFullscreen: function (event, editorId = null) {
      if (!editorId && event) {
        editorId = $(event.target)
          .closest(".fullscreen-json-btn")
          .data("editor");
      }

      const editor = this.editors[editorId];
      if (!editor) return;

      const $container = $(editor.getWrapperElement()).closest(
        ".json-editor-container"
      );

      if ($container.hasClass("fullscreen")) {
        this.exitFullscreen(editorId);
      } else {
        this.enterFullscreen(editorId);
      }
    },

    /**
     * Enter fullscreen mode
     */
    enterFullscreen: function (editorId) {
      const editor = this.editors[editorId];
      const $container = $(editor.getWrapperElement()).closest(
        ".json-editor-container"
      );

      $container.addClass("fullscreen");
      $("body").addClass("json-editor-fullscreen");

      // Update button icon
      $container
        .find(".fullscreen-json-btn .dashicons")
        .removeClass("dashicons-fullscreen-alt")
        .addClass("dashicons-fullscreen-exit-alt");

      editor.refresh();
      editor.focus();
    },

    /**
     * Exit fullscreen mode
     */
    exitFullscreen: function (editorId) {
      const editor = this.editors[editorId];
      const $container = $(editor.getWrapperElement()).closest(
        ".json-editor-container"
      );

      $container.removeClass("fullscreen");
      $("body").removeClass("json-editor-fullscreen");

      // Update button icon
      $container
        .find(".fullscreen-json-btn .dashicons")
        .removeClass("dashicons-fullscreen-exit-alt")
        .addClass("dashicons-fullscreen-alt");

      editor.refresh();
    },

    /**
     * Show status message
     */
    showStatus: function (editorId, type, message) {
      const $status = $(`#json-status-${editorId}`);
      const $indicator = $status.find(".status-indicator");
      const $text = $status.find(".status-text");

      $indicator.removeClass("success error neutral").addClass(type);
      $text.text(message);

      if (type === "error" || type === "success") {
        setTimeout(() => {
          if ($indicator.hasClass(type)) {
            $indicator.removeClass(type).addClass("neutral");
            $text.text("Ready");
          }
        }, 3000);
      }
    },
  };

  // Initialize when document is ready
  $(document).ready(function () {
    AIManagerProJSONEditor.init();
  });
})(jQuery);
