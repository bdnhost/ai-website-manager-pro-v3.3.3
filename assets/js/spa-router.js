/**
 * AI Website Manager Pro - SPA Router
 *
 * @package AI_Manager_Pro
 * @version 3.2.0
 */

(function ($) {
  "use strict";

  // Global object for SPA Router
  window.AIManagerProSPARouter = {
    currentRoute: "",
    isLoading: false,
    cache: {},

    /**
     * Initialize SPA Router
     */
    init: function () {
      this.currentRoute = aiManagerProSPA.currentRoute || "dashboard";
      this.bindEvents();
      this.initHistory();
      this.loadInitialPage();
    },

    /**
     * Bind event handlers
     */
    bindEvents: function () {
      // Navigation clicks
      $(document).on("click", ".nav-item", this.handleNavigation.bind(this));

      // Browser back/forward
      $(window).on("popstate", this.handlePopState.bind(this));

      // Retry button
      $(document).on("click", ".retry-btn", this.retryLastRequest.bind(this));

      // Preload on hover
      $(document).on("mouseenter", ".nav-item", this.preloadPage.bind(this));
    },

    /**
     * Initialize browser history
     */
    initHistory: function () {
      // Replace current state with initial route
      const initialState = {
        route: this.currentRoute,
        title: document.title,
      };

      history.replaceState(
        initialState,
        document.title,
        this.buildUrl(this.currentRoute)
      );
    },

    /**
     * Load initial page
     */
    loadInitialPage: function () {
      // If we're already on a specific route, load it
      if (this.currentRoute && this.currentRoute !== "dashboard") {
        this.loadPage(this.currentRoute, false);
      }
    },

    /**
     * Handle navigation clicks
     */
    handleNavigation: function (event) {
      event.preventDefault();

      const $link = $(event.currentTarget);
      const route = $link.data("route");

      if (route === this.currentRoute || this.isLoading) {
        return;
      }

      this.loadPage(route, true);
    },

    /**
     * Handle browser back/forward
     */
    handlePopState: function (event) {
      if (event.originalEvent.state) {
        const route = event.originalEvent.state.route;
        this.loadPage(route, false);
      }
    },

    /**
     * Load a page
     */
    loadPage: function (route, pushState = true) {
      if (this.isLoading) {
        return;
      }

      // Check cache first
      if (
        this.cache[route] &&
        this.cache[route].timestamp > Date.now() - 300000
      ) {
        // 5 minutes cache
        this.renderPage(this.cache[route].data, route, pushState);
        return;
      }

      this.isLoading = true;
      this.showLoading();
      this.updateNavigation(route);

      $.ajax({
        url: aiManagerProSPA.ajaxUrl,
        type: "POST",
        data: {
          action: "ai_manager_load_page",
          route: route,
          nonce: aiManagerProSPA.nonce,
        },
        success: (response) => {
          if (response.success) {
            // Cache the response
            this.cache[route] = {
              data: response.data,
              timestamp: Date.now(),
            };

            this.renderPage(response.data, route, pushState);
          } else {
            this.showError(response.data || aiManagerProSPA.strings.error);
          }
        },
        error: (xhr, status, error) => {
          console.error("SPA Router Error:", error);
          this.showError(aiManagerProSPA.strings.error);
        },
        complete: () => {
          this.isLoading = false;
          this.hideLoading();
        },
      });
    },

    /**
     * Render page content
     */
    renderPage: function (data, route, pushState) {
      const $content = $("#ai-spa-page-content");

      // Fade out current content
      $content.fadeOut(200, () => {
        // Update content
        $content.html(data.content);

        // Update page title
        if (data.title) {
          document.title = data.title + " - AI Website Manager Pro";
        }

        // Update URL and history
        if (pushState) {
          const url = this.buildUrl(route);
          const state = {
            route: route,
            title: data.title,
          };
          history.pushState(state, data.title, url);
        }

        // Update current route
        this.currentRoute = route;

        // Trigger page loaded event
        $(document).trigger("ai-spa-page-loaded", [route, data]);

        // Fade in new content
        $content.fadeIn(200);

        // Scroll to top
        $("html, body").animate({ scrollTop: 0 }, 300);
      });

      this.hideError();
    },

    /**
     * Update navigation active state
     */
    updateNavigation: function (route) {
      $(".nav-item").removeClass("active");
      $(`.nav-item[data-route="${route}"]`).addClass("active");
    },

    /**
     * Show loading state
     */
    showLoading: function () {
      $("#ai-spa-loading").fadeIn(200);
      $("#ai-spa-error").hide();
    },

    /**
     * Hide loading state
     */
    hideLoading: function () {
      $("#ai-spa-loading").fadeOut(200);
    },

    /**
     * Show error state
     */
    showError: function (message) {
      const $error = $("#ai-spa-error");
      $error.find(".error-message").text(message);
      $error.fadeIn(200);
      $("#ai-spa-loading").hide();
    },

    /**
     * Hide error state
     */
    hideError: function () {
      $("#ai-spa-error").fadeOut(200);
    },

    /**
     * Retry last request
     */
    retryLastRequest: function () {
      this.loadPage(this.currentRoute, false);
    },

    /**
     * Preload page on hover
     */
    preloadPage: function (event) {
      const $link = $(event.currentTarget);
      const route = $link.data("route");

      // Don't preload if already cached or currently loading
      if (this.cache[route] || this.isLoading || route === this.currentRoute) {
        return;
      }

      // Debounce preloading
      clearTimeout(this.preloadTimeout);
      this.preloadTimeout = setTimeout(() => {
        this.preloadPageContent(route);
      }, 500);
    },

    /**
     * Preload page content
     */
    preloadPageContent: function (route) {
      $.ajax({
        url: aiManagerProSPA.ajaxUrl,
        type: "POST",
        data: {
          action: "ai_manager_load_page",
          route: route,
          nonce: aiManagerProSPA.nonce,
        },
        success: (response) => {
          if (response.success) {
            // Cache the response
            this.cache[route] = {
              data: response.data,
              timestamp: Date.now(),
            };
          }
        },
        error: () => {
          // Silently fail preloading
        },
      });
    },

    /**
     * Build URL for route
     */
    buildUrl: function (route) {
      const baseUrl = window.location.pathname + window.location.search;
      const separator = baseUrl.includes("?") ? "&" : "?";
      return baseUrl + separator + "route=" + encodeURIComponent(route);
    },

    /**
     * Clear cache
     */
    clearCache: function () {
      this.cache = {};
    },

    /**
     * Navigate to route programmatically
     */
    navigateTo: function (route) {
      if (aiManagerProSPA.routes.includes(route)) {
        this.loadPage(route, true);
      }
    },

    /**
     * Get current route
     */
    getCurrentRoute: function () {
      return this.currentRoute;
    },

    /**
     * Check if route is cached
     */
    isCached: function (route) {
      return (
        this.cache[route] && this.cache[route].timestamp > Date.now() - 300000
      );
    },
  };

  // Initialize when document is ready
  $(document).ready(function () {
    // Only initialize on AI Manager Pro pages
    if ($(".ai-spa-container").length > 0) {
      AIManagerProSPARouter.init();
    }
  });

  // Expose to global scope for external access
  window.aiSpaRouter = AIManagerProSPARouter;
})(jQuery);
