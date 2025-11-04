/**
 * Prompt Library JavaScript
 * ממשק ספריית הפרומפטים
 */

class PromptLibrary {
  constructor() {
    this.currentCategory = "";
    this.currentView = "grid";
    this.prompts = [];
    this.categories = {};
    this.init();
  }

  init() {
    this.bindEvents();
    this.loadCategories();
  }

  bindEvents() {
    // פתיחת ספריית הפרומפטים
    jQuery(document).on("click", "#open-prompt-library", (e) => {
      e.preventDefault();
      this.openLibrary();
    });

    // חיפוש פרומפטים
    jQuery(document).on("input", "#prompt-search", (e) => {
      this.handleSearch(e.target.value);
    });

    // ניקוי חיפוש
    jQuery(document).on("click", "#clear-search", (e) => {
      jQuery("#prompt-search").val("");
      jQuery("#clear-search").hide();
      this.loadPrompts(this.currentCategory);
    });

    // סינון לפי קטגוריה
    jQuery(document).on("change", "#category-filter", (e) => {
      this.currentCategory = e.target.value;
      this.loadPrompts(this.currentCategory);
      this.updateCategorySelection();
    });

    // לחיצה על קטגוריה בסרגל הצד
    jQuery(document).on("click", ".category-item", (e) => {
      const category = jQuery(e.currentTarget).data("category");
      this.selectCategory(category);
    });

    // שינוי תצוגה
    jQuery(document).on("click", ".view-btn", (e) => {
      const view = jQuery(e.currentTarget).data("view");
      this.changeView(view);
    });

    // שימוש בפרומפט
    jQuery(document).on("click", ".use-prompt-btn", (e) => {
      e.preventDefault();
      const promptId = jQuery(e.currentTarget).data("prompt-id");
      this.usePrompt(promptId);
    });

    // עריכת פרומפט
    jQuery(document).on("click", ".edit-prompt-btn", (e) => {
      e.preventDefault();
      const promptId = jQuery(e.currentTarget).data("prompt-id");
      this.editPrompt(promptId);
    });

    // מחיקת פרומפט
    jQuery(document).on("click", ".delete-prompt-btn", (e) => {
      e.preventDefault();
      const promptId = jQuery(e.currentTarget).data("prompt-id");
      this.deletePrompt(promptId);
    });

    // פרומפט חדש
    jQuery(document).on("click", "#add-new-prompt", (e) => {
      e.preventDefault();
      this.openEditor();
    });

    // שמירת פרומפט
    jQuery(document).on("click", "#save-prompt-btn", (e) => {
      e.preventDefault();
      this.savePrompt();
    });

    // הוספת משתנה
    jQuery(document).on("click", "#add-variable-btn", (e) => {
      e.preventDefault();
      this.addVariable();
    });

    // הוספת תגית
    jQuery(document).on("click", "#add-tag-btn", (e) => {
      e.preventDefault();
      this.addTag();
    });

    // הסרת משתנה/תגית
    jQuery(document).on("click", ".remove-tag", (e) => {
      e.preventDefault();
      jQuery(e.currentTarget).parent().remove();
    });

    // Enter במשתנים ותגיות
    jQuery(document).on("keypress", "#variable-input, #tag-input", (e) => {
      if (e.which === 13) {
        e.preventDefault();
        if (e.target.id === "variable-input") {
          this.addVariable();
        } else {
          this.addTag();
        }
      }
    });

    // סגירת מודלים
    jQuery(document).on("click", ".modal-close, .modal-overlay", (e) => {
      if (e.target === e.currentTarget) {
        this.closeModal();
      }
    });
  }

  /**
   * פתיחת ספריית הפרומפטים
   */
  openLibrary() {
    // יצירת המודל אם לא קיים
    if (jQuery("#prompt-library-modal").length === 0) {
      this.createLibraryModal();
    }

    jQuery("#prompt-library-modal").fadeIn(300);
    this.loadPrompts(this.currentCategory);
  }

  /**
   * יצירת מודל הספרייה
   */
  createLibraryModal() {
    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_render_prompt_library_modal",
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          jQuery("body").append(response.data);
        } else {
          this.showNotification("שגיאה בטעינת הספרייה", "error");
        }
      },
      error: () => {
        this.showNotification("שגיאה בחיבור לשרת", "error");
      },
    });
  }

  /**
   * טעינת קטגוריות
   */
  loadCategories() {
    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_get_prompt_categories",
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.categories = response.data;
        }
      },
    });
  }

  /**
   * טעינת פרומפטים
   */
  loadPrompts(category = "") {
    const container = jQuery("#prompts-container");
    container.html('<div class="loading-prompts">טוען פרומפטים...</div>');

    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_get_prompts_by_category",
        category: category,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.prompts = response.data;
          this.renderPrompts(this.prompts);
        } else {
          this.showError("שגיאה בטעינת הפרומפטים");
        }
      },
      error: () => {
        this.showError("שגיאה בחיבור לשרת");
      },
    });
  }

  /**
   * רינדור פרומפטים
   */
  renderPrompts(prompts) {
    const container = jQuery("#prompts-container");

    if (prompts.length === 0) {
      jQuery("#no-prompts").show();
      container.empty();
      return;
    }

    jQuery("#no-prompts").hide();

    const promptsHtml = prompts
      .map((prompt) => this.renderPromptCard(prompt))
      .join("");
    container.html(promptsHtml);

    // עדכון מחלקת התצוגה
    container
      .removeClass("prompts-grid prompts-list")
      .addClass(`prompts-${this.currentView}`);
  }

  /**
   * רינדור כרטיס פרומפט
   */
  renderPromptCard(prompt) {
    const isUserPrompt = prompt.is_user_prompt || prompt.id.startsWith("user_");
    const categoryName =
      prompt.category_name || this.categories[prompt.category]?.name || "";

    return `
            <div class="prompt-card" data-prompt-id="${prompt.id}">
                <div class="prompt-header">
                    <div class="prompt-title-section">
                        <h4 class="prompt-title">${prompt.title}</h4>
                        ${categoryName ? `<span class="prompt-category">${categoryName}</span>` : ""}
                    </div>
                    <div class="prompt-actions">
                        <button class="action-btn use-prompt-btn" data-prompt-id="${prompt.id}" title="השתמש בפרומפט">
                            ✨
                        </button>
                        ${
                          isUserPrompt
                            ? `
                            <button class="action-btn edit-prompt-btn" data-prompt-id="${prompt.id}" title="ערוך פרומפט">
                                ✏️
                            </button>
                            <button class="action-btn delete-prompt-btn" data-prompt-id="${prompt.id}" title="מחק פרומפט">
                                🗑️
                            </button>
                        `
                            : ""
                        }
                    </div>
                </div>
                
                ${prompt.description ? `<p class="prompt-description">${prompt.description}</p>` : ""}
                
                <div class="prompt-content">
                    <div class="prompt-text">${this.truncateText(prompt.prompt, 150)}</div>
                    ${prompt.prompt.length > 150 ? '<button class="expand-btn">הרחב</button>' : ""}
                </div>
                
                <div class="prompt-meta">
                    ${
                      prompt.variables && prompt.variables.length > 0
                        ? `
                        <div class="prompt-variables">
                            <strong>משתנים:</strong>
                            ${prompt.variables.map((v) => `<span class="variable-tag">[${v}]</span>`).join("")}
                        </div>
                    `
                        : ""
                    }
                    
                    ${
                      prompt.tags && prompt.tags.length > 0
                        ? `
                        <div class="prompt-tags">
                            ${prompt.tags.map((tag) => `<span class="tag">${tag}</span>`).join("")}
                        </div>
                    `
                        : ""
                    }
                    
                    <div class="prompt-stats">
                        <span class="usage-count">שימושים: ${prompt.usage_count || 0}</span>
                        ${isUserPrompt ? '<span class="user-prompt-badge">אישי</span>' : ""}
                    </div>
                </div>
            </div>
        `;
  }

  /**
   * חיפוש פרומפטים
   */
  handleSearch(query) {
    const searchBtn = jQuery("#clear-search");

    if (query.length > 0) {
      searchBtn.show();

      if (query.length >= 2) {
        this.searchPrompts(query);
      }
    } else {
      searchBtn.hide();
      this.loadPrompts(this.currentCategory);
    }
  }

  /**
   * ביצוע חיפוש
   */
  searchPrompts(query) {
    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_search_prompts",
        query: query,
        category: this.currentCategory,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.renderPrompts(response.data);
        }
      },
    });
  }

  /**
   * בחירת קטגוריה
   */
  selectCategory(category) {
    this.currentCategory = category;

    // עדכון UI
    jQuery(".category-item").removeClass("active");
    jQuery(`.category-item[data-category="${category}"]`).addClass("active");

    // עדכון כותרת
    const categoryName = category
      ? this.categories[category]?.name
      : "כל הפרומפטים";
    jQuery("#current-category-title").text(categoryName);

    // עדכון סלקט
    jQuery("#category-filter").val(category);

    // טעינת פרומפטים
    this.loadPrompts(category);
  }

  /**
   * שינוי תצוגה
   */
  changeView(view) {
    this.currentView = view;

    jQuery(".view-btn").removeClass("active");
    jQuery(`.view-btn[data-view="${view}"]`).addClass("active");

    const container = jQuery("#prompts-container");
    container
      .removeClass("prompts-grid prompts-list")
      .addClass(`prompts-${view}`);
  }

  /**
   * שימוש בפרומפט
   */
  usePrompt(promptId) {
    // רישום השימוש
    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_use_prompt",
        prompt_id: promptId,
        nonce: ai_website_manager_ajax.nonce,
      },
    });

    // קבלת הפרומפט והעתקה
    const prompt = this.prompts.find((p) => p.id === promptId);
    if (prompt) {
      this.insertPromptToEditor(prompt);
      this.closeModal();
    }
  }

  /**
   * הכנסת פרומפט לעורך
   */
  insertPromptToEditor(prompt) {
    // חיפוש שדה טקסט פעיל
    const activeTextarea = jQuery(
      'textarea:focus, input[type="text"]:focus'
    ).first();

    if (activeTextarea.length > 0) {
      const currentValue = activeTextarea.val();
      const newValue =
        currentValue + (currentValue ? "\n\n" : "") + prompt.prompt;
      activeTextarea.val(newValue);

      this.showNotification("הפרומפט הוכנס בהצלחה!", "success");
    } else {
      // העתקה ללוח
      this.copyToClipboard(prompt.prompt);
      this.showNotification("הפרומפט הועתק ללוח!", "success");
    }
  }

  /**
   * פתיחת עורך פרומפטים
   */
  openEditor(promptId = null) {
    if (promptId) {
      // עריכת פרומפט קיים
      this.loadPromptForEdit(promptId);
    } else {
      // פרומפט חדש
      this.resetEditor();
      jQuery("#editor-title").text("✏️ פרומפט חדש");
    }

    jQuery("#prompt-editor-modal").fadeIn(300);
  }

  /**
   * טעינת פרומפט לעריכה
   */
  loadPromptForEdit(promptId) {
    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_get_prompt_by_id",
        prompt_id: promptId,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.populateEditor(response.data);
          jQuery("#editor-title").text("✏️ עריכת פרומפט");
        } else {
          this.showNotification("שגיאה בטעינת הפרומפט", "error");
        }
      },
    });
  }

  /**
   * מילוי עורך בנתוני פרומפט
   */
  populateEditor(prompt) {
    jQuery("#prompt-id").val(prompt.id);
    jQuery("#prompt-title").val(prompt.title);
    jQuery("#prompt-description").val(prompt.description || "");
    jQuery("#prompt-content").val(prompt.prompt);

    // משתנים
    const variablesList = jQuery("#variables-list");
    variablesList.empty();
    if (prompt.variables) {
      prompt.variables.forEach((variable) => {
        this.addVariableTag(variable);
      });
    }

    // תגיות
    const tagsList = jQuery("#tags-list");
    tagsList.empty();
    if (prompt.tags) {
      prompt.tags.forEach((tag) => {
        this.addTagElement(tag);
      });
    }

    // סוגי תוכן
    if (prompt.content_types) {
      jQuery("#prompt-content-types").val(prompt.content_types);
    }
  }

  /**
   * איפוס עורך
   */
  resetEditor() {
    jQuery("#prompt-editor-form")[0].reset();
    jQuery("#prompt-id").val("");
    jQuery("#variables-list").empty();
    jQuery("#tags-list").empty();
  }

  /**
   * שמירת פרומפט
   */
  savePrompt() {
    const formData = this.collectEditorData();

    if (!this.validatePromptData(formData)) {
      return;
    }

    const promptId = jQuery("#prompt-id").val();
    const action = promptId ? "ai_update_user_prompt" : "ai_save_user_prompt";

    const data = {
      action: action,
      nonce: ai_website_manager_ajax.nonce,
      ...formData,
    };

    if (promptId) {
      data.prompt_id = promptId;
    }

    const button = jQuery("#save-prompt-btn");
    const originalText = button.text();
    button.prop("disabled", true).text("שומר...");

    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: data,
      success: (response) => {
        if (response.success) {
          this.showNotification("הפרומפט נשמר בהצלחה!", "success");
          this.closeEditor();
          this.loadPrompts(this.currentCategory);
        } else {
          this.showNotification("שגיאה: " + response.data, "error");
        }
      },
      error: () => {
        this.showNotification("שגיאה בשמירה", "error");
      },
      complete: () => {
        button.prop("disabled", false).text(originalText);
      },
    });
  }

  /**
   * איסוף נתוני עורך
   */
  collectEditorData() {
    const variables = [];
    jQuery("#variables-list .tag").each(function () {
      variables.push(jQuery(this).text().replace("×", "").trim());
    });

    const tags = [];
    jQuery("#tags-list .tag").each(function () {
      tags.push(jQuery(this).text().replace("×", "").trim());
    });

    return {
      title: jQuery("#prompt-title").val(),
      description: jQuery("#prompt-description").val(),
      prompt: jQuery("#prompt-content").val(),
      variables: variables,
      content_types: jQuery("#prompt-content-types").val() || [],
      tags: tags,
    };
  }

  /**
   * ולידציה של נתוני פרומפט
   */
  validatePromptData(data) {
    if (!data.title.trim()) {
      this.showNotification("כותרת הפרומפט חובה", "error");
      return false;
    }

    if (!data.prompt.trim()) {
      this.showNotification("תוכן הפרומפט חובה", "error");
      return false;
    }

    if (data.title.length > 200) {
      this.showNotification("כותרת ארוכה מדי (מקסימום 200 תווים)", "error");
      return false;
    }

    if (data.prompt.length > 5000) {
      this.showNotification("פרומפט ארוך מדי (מקסימום 5000 תווים)", "error");
      return false;
    }

    return true;
  }

  /**
   * הוספת משתנה
   */
  addVariable() {
    const input = jQuery("#variable-input");
    const value = input.val().trim();

    if (value) {
      this.addVariableTag(value);
      input.val("");
    }
  }

  /**
   * הוספת תג משתנה
   */
  addVariableTag(variable) {
    const tag = jQuery(`
            <span class="tag variable-tag">
                ${variable}
                <button type="button" class="remove-tag">×</button>
            </span>
        `);

    jQuery("#variables-list").append(tag);
  }

  /**
   * הוספת תגית
   */
  addTag() {
    const input = jQuery("#tag-input");
    const value = input.val().trim();

    if (value) {
      this.addTagElement(value);
      input.val("");
    }
  }

  /**
   * הוספת אלמנט תגית
   */
  addTagElement(tag) {
    const tagElement = jQuery(`
            <span class="tag">
                ${tag}
                <button type="button" class="remove-tag">×</button>
            </span>
        `);

    jQuery("#tags-list").append(tagElement);
  }

  /**
   * מחיקת פרומפט
   */
  deletePrompt(promptId) {
    if (!confirm("האם אתה בטוח שברצונך למחוק את הפרומפט?")) {
      return;
    }

    jQuery.ajax({
      url: ai_website_manager_ajax.ajax_url,
      type: "POST",
      data: {
        action: "ai_delete_user_prompt",
        prompt_id: promptId,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          this.showNotification("הפרומפט נמחק בהצלחה", "success");
          this.loadPrompts(this.currentCategory);
        } else {
          this.showNotification("שגיאה במחיקה: " + response.data, "error");
        }
      },
      error: () => {
        this.showNotification("שגיאה במחיקה", "error");
      },
    });
  }

  /**
   * עריכת פרומפט
   */
  editPrompt(promptId) {
    this.openEditor(promptId);
  }

  /**
   * סגירת עורך
   */
  closeEditor() {
    jQuery("#prompt-editor-modal").fadeOut(300);
  }

  /**
   * סגירת מודל
   */
  closeModal() {
    jQuery(".ai-modal").fadeOut(300);
  }

  /**
   * קיצור טקסט
   */
  truncateText(text, maxLength) {
    if (text.length <= maxLength) {
      return text;
    }
    return text.substring(0, maxLength) + "...";
  }

  /**
   * העתקה ללוח
   */
  copyToClipboard(text) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text);
    } else {
      // Fallback for older browsers
      const textArea = document.createElement("textarea");
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand("copy");
      document.body.removeChild(textArea);
    }
  }

  /**
   * הצגת שגיאה
   */
  showError(message) {
    jQuery("#prompts-container").html(`
            <div class="error-message">
                <div class="error-icon">⚠️</div>
                <h4>שגיאה</h4>
                <p>${message}</p>
            </div>
        `);
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
  window.promptLibrary = new PromptLibrary();
});
