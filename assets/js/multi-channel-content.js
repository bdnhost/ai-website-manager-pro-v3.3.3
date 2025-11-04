/**
 * Multi-Channel Content JavaScript
 * קוד JavaScript לניהול תוכן רב-ערוצי
 */

jQuery(document).ready(function ($) {
  // אתחול האירועים
  initializeMultiChannelContent();

  /**
   * אתחול כל האירועים
   */
  function initializeMultiChannelContent() {
    // אירועי בחירת ערוצים
    $(".channel-checkbox").on("change", function () {
      const $card = $(this).closest(".channel-card");
      const $options = $card.find(".channel-options");

      if ($(this).is(":checked")) {
        $card.addClass("selected");
        $options.slideDown(300);
      } else {
        $card.removeClass("selected");
        $options.slideUp(300);
      }

      updateContentPlanPreview();
    });

    // אירועי שינוי בהגדרות
    $(".content-count, .create-variations").on(
      "change",
      updateContentPlanPreview
    );
    $("#selected-brand").on("change", updateBrandInfo);

    // אתחול ברירת מחדל
    if ($("#selected-brand").val()) {
      updateBrandInfo();
    }
  }

  /**
   * עדכון מידע המותג
   */
  window.updateBrandInfo = function () {
    const $select = $("#selected-brand");
    const $brandInfo = $("#brand-info");
    const selectedOption = $select.find("option:selected");

    if ($select.val()) {
      const industry = selectedOption.data("industry");
      const keywords = selectedOption.data("keywords");

      $brandInfo.find(".brand-industry").text(industry || "לא צוין");
      $brandInfo
        .find(".brand-keywords")
        .text(Array.isArray(keywords) ? keywords.join(", ") : "לא צוינו");

      $brandInfo.slideDown(300);
    } else {
      $brandInfo.slideUp(300);
    }

    updateContentPlanPreview();
  };

  /**
   * בחירת כל הערוצים
   */
  window.selectAllChannels = function () {
    $(".channel-checkbox").prop("checked", true).trigger("change");
  };

  /**
   * ניקוי בחירת ערוצים
   */
  window.clearChannelSelection = function () {
    $(".channel-checkbox").prop("checked", false).trigger("change");
  };

  /**
   * הצגת תבניות ערוצים
   */
  window.showChannelPresets = function () {
    $("#channel-presets-modal").fadeIn(300);
  };

  /**
   * סגירת modal תבניות
   */
  window.closeChannelPresetsModal = function () {
    $("#channel-presets-modal").fadeOut(300);
  };

  /**
   * החלת תבנית ערוצים
   */
  window.applyChannelPreset = function (presetType) {
    // ניקוי בחירה קיימת
    clearChannelSelection();

    const presets = {
      startup: ["blog_posts", "social_media", "email_marketing"],
      ecommerce: ["product_content", "ad_copy", "social_media"],
      corporate: ["blog_posts", "press_releases", "email_marketing"],
      content_creator: ["social_media", "video_scripts", "blog_posts"],
      all_channels: Object.keys(getAvailableChannels()),
    };

    const channels = presets[presetType] || [];

    channels.forEach((channel) => {
      $(`#channel-${channel}`).prop("checked", true).trigger("change");
    });

    closeChannelPresetsModal();
    showNotification(`תבנית "${presetType}" הוחלה בהצלחה!`, "success");
  };

  /**
   * הצגת/הסתרת הגדרות מתקדמות
   */
  window.toggleAdvancedSettings = function () {
    const $button = $(".toggle-advanced");
    const $options = $(".advanced-options");
    const $text = $button.find(".toggle-text");

    if ($options.is(":visible")) {
      $options.slideUp(300);
      $text.text("הצג הגדרות מתקדמות");
      $button.removeClass("expanded");
    } else {
      $options.slideDown(300);
      $text.text("הסתר הגדרות מתקדמות");
      $button.addClass("expanded");
    }
  };

  /**
   * עדכון תצוגה מקדימה של התוכנית
   */
  function updateContentPlanPreview() {
    const selectedChannels = getSelectedChannels();
    const totalContent = calculateTotalContent(selectedChannels);
    const estimatedTime = calculateEstimatedTime(totalContent);

    $("#selected-channels-count").text(selectedChannels.length);
    $("#total-content-count").text(totalContent);
    $("#estimated-time").text(estimatedTime + " דקות");

    // הצגת פרטי התוכנית
    const planDetails = buildPlanDetails(selectedChannels);
    $("#plan-details").html(planDetails);

    if (selectedChannels.length > 0) {
      $("#content-plan-preview").slideDown(300);
    } else {
      $("#content-plan-preview").slideUp(300);
    }
  }

  /**
   * תצוגה מקדימה של התוכנית
   */
  window.previewContentPlan = function () {
    updateContentPlanPreview();

    if ($("#content-plan-preview").is(":visible")) {
      $("html, body").animate(
        {
          scrollTop: $("#content-plan-preview").offset().top - 100,
        },
        500
      );
    } else {
      showNotification("אנא בחר לפחות ערוץ אחד", "warning");
    }
  };

  /**
   * יצירת תוכן רב-ערוצי
   */
  window.generateMultiChannelContent = function () {
    const brandId = $("#selected-brand").val();
    const selectedChannels = getSelectedChannels();

    if (!brandId) {
      showNotification("אנא בחר מותג", "error");
      return;
    }

    if (selectedChannels.length === 0) {
      showNotification("אנא בחר לפחות ערוץ אחד", "error");
      return;
    }

    const contentPlan = buildContentPlan(selectedChannels);
    const isAsync = $("#async-generation").is(":checked");

    // הצגת מצב טעינה
    const $button = $("#generate-btn");
    const originalText = $button.text();
    $button.text("🔄 יוצר תוכן...").prop("disabled", true);

    // הצגת progress bar
    showProgressBar();

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "generate_multi_channel_content",
        brand_id: brandId,
        content_plan: JSON.stringify(contentPlan),
        async_generation: isAsync,
        nonce: ai_website_manager_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          displayContentResults(response.data);
          showNotification("התוכן נוצר בהצלחה!", "success");
        } else {
          showNotification("שגיאה ביצירת התוכן: " + response.data, "error");
        }
      },
      error: function () {
        showNotification("שגיאה בחיבור לשרת", "error");
      },
      complete: function () {
        $button.text(originalText).prop("disabled", false);
        hideProgressBar();
      },
    });
  };

  /**
   * קבלת ערוצים נבחרים
   */
  function getSelectedChannels() {
    const channels = [];

    $(".channel-checkbox:checked").each(function () {
      const channelId = $(this).val();
      const contentCount =
        $(`.content-count[data-channel="${channelId}"]`).val() || 5;
      const createVariations = $(
        `.create-variations[data-channel="${channelId}"]`
      ).is(":checked");

      channels.push({
        id: channelId,
        name: $(this).closest(".channel-card").find(".channel-name").text(),
        contentCount: parseInt(contentCount),
        createVariations: createVariations,
      });
    });

    return channels;
  }

  /**
   * חישוב סך התכנים
   */
  function calculateTotalContent(channels) {
    return channels.reduce((total, channel) => {
      const baseContent = channel.contentCount;
      const variations = channel.createVariations ? baseContent * 2 : 0;
      return total + baseContent + variations;
    }, 0);
  }

  /**
   * חישוב זמן משוער
   */
  function calculateEstimatedTime(totalContent) {
    // הערכה של 2 דקות לכל תוכן
    return Math.ceil(totalContent * 2);
  }

  /**
   * בניית פרטי התוכנית
   */
  function buildPlanDetails(channels) {
    let html = '<div class="plan-channels">';

    channels.forEach((channel) => {
      const variations = channel.createVariations
        ? ` + ${channel.contentCount} וריאציות`
        : "";

      html += `
                <div class="plan-channel">
                    <div class="plan-channel-header">
                        <span class="channel-icon">${getChannelIcon(
                          channel.id
                        )}</span>
                        <span class="channel-name">${channel.name}</span>
                        <span class="channel-count">${
                          channel.contentCount
                        } תכנים${variations}</span>
                    </div>
                </div>
            `;
    });

    html += "</div>";
    return html;
  }

  /**
   * בניית תוכנית התוכן
   */
  function buildContentPlan(channels) {
    const plan = {};

    channels.forEach((channel) => {
      plan[channel.id] = {
        default_type: getDefaultTypeForChannel(channel.id),
        topics: generateTopicsForChannel(channel.id, channel.contentCount),
        options: {
          create_variations: channel.createVariations,
          target_length: getDefaultLengthForChannel(channel.id),
          tone_adjustment: $("#content-tone").val(),
          keywords: $("#additional-keywords")
            .val()
            .split(",")
            .map((k) => k.trim())
            .filter((k) => k),
          target_audience: $("#target-audience").val(),
          content_focus: $("#content-focus").val(),
          include_cta: $("#include-cta").is(":checked"),
          seo_optimize: $("#seo-optimize").is(":checked"),
        },
      };
    });

    return plan;
  }

  /**
   * הצגת תוצאות התוכן
   */
  function displayContentResults(results) {
    const $container = $("#results-container");
    let html = "";

    if (results.success) {
      html += `
                <div class="results-header">
                    <h3>🎉 נוצרו ${results.total_content_pieces} תכנים עבור ${
        results.brand_name
      }</h3>
                    <p class="results-time">נוצר ב: ${formatDate(
                      results.generated_at
                    )}</p>
                </div>
            `;

      Object.entries(results.channels).forEach(([channelId, channelData]) => {
        html += `
                    <div class="channel-results">
                        <div class="channel-results-header">
                            <h4>${channelData.icon} ${channelData.channel_name}</h4>
                            <span class="content-count-badge">${channelData.content_pieces} תכנים</span>
                        </div>
                        <div class="channel-content-list">
                `;

        channelData.results.forEach((contentResult, index) => {
          if (contentResult.success) {
            html += `
                            <div class="content-item">
                                <div class="content-header">
                                    <h5>${
                                      contentResult.metadata?.title ||
                                      `תוכן ${index + 1}`
                                    }</h5>
                                    <div class="content-actions">
                                        <button class="ai-btn ai-btn-sm ai-btn-outline" onclick="copyContent(${index})">
                                            📋 העתק
                                        </button>
                                        <button class="ai-btn ai-btn-sm ai-btn-info" onclick="previewContent(${index})">
                                            👁️ תצוגה
                                        </button>
                                    </div>
                                </div>
                                <div class="content-preview" id="content-${index}" style="display: none;">
                                    ${contentResult.content}
                                </div>
                            </div>
                        `;
          }
        });

        html += `
                        </div>
                    </div>
                `;
      });
    } else {
      html = `
                <div class="results-error">
                    <h3>❌ שגיאה ביצירת התוכן</h3>
                    <p>${results.error}</p>
                </div>
            `;
    }

    $container.html(html);
    $("#content-results").slideDown(300);

    // גלילה לתוצאות
    $("html, body").animate(
      {
        scrollTop: $("#content-results").offset().top - 100,
      },
      500
    );
  }

  /**
   * פונקציות עזר
   */
  function getChannelIcon(channelId) {
    const icons = {
      blog_posts: "📝",
      social_media: "📱",
      email_marketing: "📧",
      product_content: "🛍️",
      video_scripts: "🎬",
      press_releases: "📰",
      landing_pages: "🎯",
      ad_copy: "📢",
    };
    return icons[channelId] || "📄";
  }

  function getDefaultTypeForChannel(channelId) {
    const types = {
      blog_posts: "how_to",
      social_media: "facebook",
      email_marketing: "newsletter",
      product_content: "description",
      video_scripts: "explainer",
      press_releases: "announcement",
      landing_pages: "sales",
      ad_copy: "google_ads",
    };
    return types[channelId] || "general";
  }

  function getDefaultLengthForChannel(channelId) {
    const lengths = {
      blog_posts: 800,
      social_media: 150,
      email_marketing: 300,
      product_content: 200,
      video_scripts: 500,
      press_releases: 400,
      landing_pages: 600,
      ad_copy: 100,
    };
    return lengths[channelId] || 300;
  }

  function generateTopicsForChannel(channelId, count) {
    // זוהי פונקציה בסיסית - בפועל תגיע מהשרת
    const baseTopics = [
      "טיפים מקצועיים",
      "מגמות בתחום",
      "מדריך מעשי",
      "שגיאות נפוצות",
      "עתיד התחום",
      "סיפורי הצלחה",
      "השוואה בין אפשרויות",
      "מה חדש בתחום",
    ];

    return baseTopics.slice(0, count);
  }

  function getAvailableChannels() {
    // רשימת ערוצים זמינים
    return {
      blog_posts: "פוסטי בלוג",
      social_media: "רשתות חברתיות",
      email_marketing: "שיווק באימייל",
      product_content: "תוכן מוצרים",
      video_scripts: "תסריטי וידאו",
      press_releases: "הודעות לעיתונות",
      landing_pages: "דפי נחיתה",
      ad_copy: "קופי לפרסומות",
    };
  }

  function showProgressBar() {
    // הצגת progress bar
    const progressHtml = `
            <div id="content-progress" class="progress-overlay">
                <div class="progress-content">
                    <h3>🚀 יוצר תוכן רב-ערוצי</h3>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <p class="progress-text">מעבד את הבקשה...</p>
                </div>
            </div>
        `;

    $("body").append(progressHtml);

    // אנימציה של progress bar
    let progress = 0;
    const interval = setInterval(() => {
      progress += Math.random() * 10;
      if (progress > 90) progress = 90;

      $(".progress-fill").css("width", progress + "%");

      if (progress > 30) $(".progress-text").text("יוצר תוכן...");
      if (progress > 60) $(".progress-text").text("מעצב תבניות...");
      if (progress > 80) $(".progress-text").text("מסיים...");
    }, 500);

    // שמירת interval למחיקה מאוחרת
    $("#content-progress").data("interval", interval);
  }

  function hideProgressBar() {
    const $progress = $("#content-progress");
    const interval = $progress.data("interval");

    if (interval) {
      clearInterval(interval);
    }

    // השלמת progress bar ומחיקה
    $(".progress-fill").css("width", "100%");
    $(".progress-text").text("הושלם!");

    setTimeout(() => {
      $progress.fadeOut(300, function () {
        $(this).remove();
      });
    }, 1000);
  }

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString("he-IL");
  }

  // פונקציות גלובליות נוספות
  window.copyContent = function (index) {
    const content = $(`#content-${index}`).text();
    copyToClipboard(content);
  };

  window.previewContent = function (index) {
    $(`#content-${index}`).slideToggle(300);
  };

  window.downloadAllContent = function () {
    showNotification("הורדת תוכן - בפיתוח", "info");
  };

  window.exportToWordPress = function () {
    showNotification("ייצוא ל-WordPress - בפיתוח", "info");
  };

  window.shareResults = function () {
    showNotification("שיתוף תוצאות - בפיתוח", "info");
  };

  window.saveContentPlan = function () {
    showNotification("שמירת תוכנית - בפיתוח", "info");
  };

  window.loadContentPlan = function () {
    showNotification("טעינת תוכנית - בפיתוח", "info");
  };
});
