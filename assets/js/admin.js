/**
 * AI Website Manager Pro - Admin JavaScript
 *
 * @package AI_Manager_Pro
 * @version 2.0.0
 */

(function($) {
    'use strict';

    // Global object for AI Manager Pro
    window.AIManagerPro = {
        init: function() {
            this.bindEvents();
            this.initComponents();
        },

        bindEvents: function() {
            // Content generation form
            $(document).on('submit', '#ai-content-form', this.handleContentGeneration);
            
            // Brand import/export
            $(document).on('click', '#import-brand-btn', this.handleBrandImport);
            $(document).on('click', '#export-brand-btn', this.handleBrandExport);
            $(document).on('click', '#get-template-btn', this.handleGetBrandTemplate);
            
            // Automation management
            $(document).on('submit', '#automation-form', this.handleCreateAutomation);
            $(document).on('click', '.run-automation-btn', this.handleRunAutomation);
            
            // API connection testing
            $(document).on('click', '.test-connection-btn', this.handleTestConnection);
            
            // Model selection for OpenRouter
            $(document).on('change', '#ai-provider-select', this.handleProviderChange);
        },

        initComponents: function() {
            // Initialize tooltips
            if (typeof $.fn.tooltip !== 'undefined') {
                $('[data-toggle="tooltip"]').tooltip();
            }
            
            // Initialize code editors if available
            this.initCodeEditors();
            
            // Load initial data
            this.loadProviderModels();
        },

        handleContentGeneration: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();
            
            // Show loading state
            $submitBtn.prop('disabled', true).text('Generating...');
            
            const formData = {
                action: 'ai_manager_pro_action',
                action_type: 'generate_content',
                nonce: aiManagerPro.nonce,
                topic: $form.find('#topic').val(),
                content_type: $form.find('#content-type').val(),
                brand_id: $form.find('#brand-id').val(),
                ai_model: $form.find('#ai-model').val(),
                auto_publish: $form.find('#auto-publish').is(':checked'),
                post_status: $form.find('#post-status').val()
            };

            $.post(aiManagerPro.ajaxUrl, formData)
                .done(function(response) {
                    if (response.success) {
                        AIManagerPro.showNotice('Content generated successfully!', 'success');
                        
                        // Display generated content
                        if (response.data.content) {
                            $('#generated-content').html(response.data.content.content);
                            $('#content-results').show();
                        }
                        
                        // Reset form
                        $form[0].reset();
                    } else {
                        AIManagerPro.showNotice('Error: ' + (response.data || 'Unknown error'), 'error');
                    }
                })
                .fail(function() {
                    AIManagerPro.showNotice('Network error occurred', 'error');
                })
                .always(function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                });
        },

        handleBrandImport: function(e) {
            e.preventDefault();
            
            const fileInput = $('#brand-file')[0];
            const jsonData = $('#brand-json').val();
            
            if (!fileInput.files.length && !jsonData.trim()) {
                AIManagerPro.showNotice('Please select a file or enter JSON data', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'ai_manager_pro_import_brand');
            formData.append('nonce', aiManagerPro.nonce);
            
            if (fileInput.files.length) {
                formData.append('brand_file', fileInput.files[0]);
            } else {
                formData.append('json_data', jsonData);
            }
            
            $.ajax({
                url: aiManagerPro.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        AIManagerPro.showNotice('Brand imported successfully!', 'success');
                        // Refresh brand list
                        location.reload();
                    } else {
                        AIManagerPro.showNotice('Import failed: ' + (response.data || 'Unknown error'), 'error');
                    }
                },
                error: function() {
                    AIManagerPro.showNotice('Network error occurred', 'error');
                }
            });
        },

        handleBrandExport: function(e) {
            e.preventDefault();
            
            const brandId = $(this).data('brand-id');
            if (!brandId) {
                AIManagerPro.showNotice('No brand selected', 'error');
                return;
            }
            
            const url = aiManagerPro.ajaxUrl + '?action=ai_manager_pro_export_brand&brand_id=' + brandId + '&nonce=' + aiManagerPro.nonce;
            window.open(url, '_blank');
        },

        handleGetBrandTemplate: function(e) {
            e.preventDefault();
            
            const data = {
                action: 'ai_manager_pro_get_brand_template',
                nonce: aiManagerPro.nonce
            };
            
            $.post(aiManagerPro.ajaxUrl, data)
                .done(function(response) {
                    if (response.success) {
                        $('#brand-json').val(response.data.template);
                        AIManagerPro.showNotice('Template loaded successfully!', 'success');
                    } else {
                        AIManagerPro.showNotice('Failed to load template', 'error');
                    }
                })
                .fail(function() {
                    AIManagerPro.showNotice('Network error occurred', 'error');
                });
        },

        handleCreateAutomation: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();
            
            $submitBtn.prop('disabled', true).text('Creating...');
            
            const formData = {
                action: 'ai_manager_pro_create_automation',
                nonce: aiManagerPro.nonce,
                name: $form.find('#automation-name').val(),
                description: $form.find('#automation-description').val(),
                content_type: $form.find('#automation-content-type').val(),
                schedule: JSON.stringify(AIManagerPro.getScheduleData($form)),
                content_options: JSON.stringify(AIManagerPro.getContentOptionsData($form)),
                conditions: JSON.stringify(AIManagerPro.getConditionsData($form)),
                actions: JSON.stringify(AIManagerPro.getActionsData($form)),
                notifications: JSON.stringify(AIManagerPro.getNotificationsData($form))
            };
            
            $.post(aiManagerPro.ajaxUrl, formData)
                .done(function(response) {
                    if (response.success) {
                        AIManagerPro.showNotice('Automation created successfully!', 'success');
                        $form[0].reset();
                        // Refresh automation list
                        location.reload();
                    } else {
                        AIManagerPro.showNotice('Error: ' + (response.data || 'Unknown error'), 'error');
                    }
                })
                .fail(function() {
                    AIManagerPro.showNotice('Network error occurred', 'error');
                })
                .always(function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                });
        },

        handleRunAutomation: function(e) {
            e.preventDefault();
            
            const taskId = $(this).data('task-id');
            const $btn = $(this);
            const originalText = $btn.text();
            
            $btn.prop('disabled', true).text('Running...');
            
            const data = {
                action: 'ai_manager_pro_run_automation',
                nonce: aiManagerPro.nonce,
                task_id: taskId
            };
            
            $.post(aiManagerPro.ajaxUrl, data)
                .done(function(response) {
                    if (response.success) {
                        AIManagerPro.showNotice('Automation executed successfully!', 'success');
                    } else {
                        AIManagerPro.showNotice('Execution failed: ' + (response.data || 'Unknown error'), 'error');
                    }
                })
                .fail(function() {
                    AIManagerPro.showNotice('Network error occurred', 'error');
                })
                .always(function() {
                    $btn.prop('disabled', false).text(originalText);
                });
        },

        handleTestConnection: function(e) {
            e.preventDefault();
            
            const provider = $(this).data('provider');
            const $btn = $(this);
            const originalText = $btn.text();
            
            $btn.prop('disabled', true).text('Testing...');
            
            const data = {
                action: 'ai_manager_pro_action',
                action_type: 'test_api_connection',
                nonce: aiManagerPro.nonce,
                provider: provider
            };
            
            $.post(aiManagerPro.ajaxUrl, data)
                .done(function(response) {
                    if (response.success) {
                        const status = response.data.connected ? 'Connected' : 'Failed';
                        const type = response.data.connected ? 'success' : 'error';
                        AIManagerPro.showNotice('Connection test: ' + status, type);
                    } else {
                        AIManagerPro.showNotice('Test failed: ' + (response.data || 'Unknown error'), 'error');
                    }
                })
                .fail(function() {
                    AIManagerPro.showNotice('Network error occurred', 'error');
                })
                .always(function() {
                    $btn.prop('disabled', false).text(originalText);
                });
        },

        handleProviderChange: function() {
            const provider = $(this).val();
            AIManagerPro.loadProviderModels(provider);
        },

        loadProviderModels: function(provider) {
            provider = provider || $('#ai-provider-select').val();
            
            if (!provider) return;
            
            const $modelSelect = $('#ai-model-select');
            $modelSelect.prop('disabled', true).html('<option>Loading...</option>');
            
            // This would typically make an AJAX call to get available models
            // For now, we'll use static data
            const models = AIManagerPro.getModelsForProvider(provider);
            
            $modelSelect.empty();
            $modelSelect.append('<option value="">Select Model</option>');
            
            models.forEach(function(model) {
                $modelSelect.append('<option value="' + model.id + '">' + model.name + '</option>');
            });
            
            $modelSelect.prop('disabled', false);
        },

        getModelsForProvider: function(provider) {
            const modelData = {
                'openai': [
                    { id: 'gpt-4', name: 'GPT-4' },
                    { id: 'gpt-3.5-turbo', name: 'GPT-3.5 Turbo' }
                ],
                'claude': [
                    { id: 'claude-3-haiku', name: 'Claude 3 Haiku' },
                    { id: 'claude-3-sonnet', name: 'Claude 3 Sonnet' }
                ],
                'openrouter': [
                    { id: 'openai/gpt-4', name: 'OpenAI GPT-4' },
                    { id: 'openai/gpt-3.5-turbo', name: 'OpenAI GPT-3.5 Turbo' },
                    { id: 'anthropic/claude-3-haiku', name: 'Anthropic Claude 3 Haiku' },
                    { id: 'anthropic/claude-3-sonnet', name: 'Anthropic Claude 3 Sonnet' },
                    { id: 'meta-llama/llama-2-70b-chat', name: 'Meta Llama 2 70B Chat' },
                    { id: 'google/gemini-pro', name: 'Google Gemini Pro' }
                ]
            };
            
            return modelData[provider] || [];
        },

        getScheduleData: function($form) {
            return {
                type: $form.find('#schedule-type').val(),
                frequency: $form.find('#schedule-frequency').val(),
                time: $form.find('#schedule-time').val(),
                day: $form.find('#schedule-day').val(),
                datetime: $form.find('#schedule-datetime').val(),
                strategy: $form.find('#schedule-strategy').val(),
                timezone: $form.find('#schedule-timezone').val() || 'UTC'
            };
        },

        getContentOptionsData: function($form) {
            const topics = $form.find('#automation-topics').val().split('\n').filter(t => t.trim());
            
            return {
                topics: topics,
                brand_id: $form.find('#automation-brand-id').val(),
                ai_provider: $form.find('#automation-ai-provider').val(),
                ai_model: $form.find('#automation-ai-model').val(),
                auto_publish: $form.find('#automation-auto-publish').is(':checked'),
                post_status: $form.find('#automation-post-status').val(),
                post_category: $form.find('#automation-post-category').val()
            };
        },

        getConditionsData: function($form) {
            // This would collect condition data from dynamic form fields
            return [];
        },

        getActionsData: function($form) {
            // This would collect action data from dynamic form fields
            return [];
        },

        getNotificationsData: function($form) {
            const notifications = [];
            
            if ($form.find('#notify-email').is(':checked')) {
                notifications.push({
                    type: 'email',
                    email: $form.find('#notification-email').val()
                });
            }
            
            if ($form.find('#notify-admin').is(':checked')) {
                notifications.push({
                    type: 'admin_notice'
                });
            }
            
            return notifications;
        },

        initCodeEditors: function() {
            // Initialize code editors for JSON input
            if (typeof CodeMirror !== 'undefined') {
                const jsonTextareas = document.querySelectorAll('textarea[data-mode="json"]');
                jsonTextareas.forEach(function(textarea) {
                    CodeMirror.fromTextArea(textarea, {
                        mode: 'application/json',
                        theme: 'default',
                        lineNumbers: true,
                        autoCloseBrackets: true,
                        matchBrackets: true,
                        indentUnit: 2,
                        tabSize: 2
                    });
                });
            }
        },

        showNotice: function(message, type) {
            type = type || 'info';
            
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            
            // Add dismiss button
            $notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');
            
            // Insert notice
            $('.wrap h1').after($notice);
            
            // Handle dismiss
            $notice.find('.notice-dismiss').on('click', function() {
                $notice.fadeOut();
            });
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
        },

        formatJSON: function(jsonString) {
            try {
                const parsed = JSON.parse(jsonString);
                return JSON.stringify(parsed, null, 2);
            } catch (e) {
                return jsonString;
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        AIManagerPro.init();
    });

})(jQuery);

