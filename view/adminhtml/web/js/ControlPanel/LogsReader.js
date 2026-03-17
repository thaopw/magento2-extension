define([
    'jquery',
    'mage/translate',
    'prototype'
], function($, $t) {
    'use strict';

    /**
     * @typedef {Object} LogsReaderOptions
     * @property {string} getFileContentUrl - URL to fetch log content
     * @property {number} pageSize - Number of lines to load per request
     * @property {string} defaultLogFile - exception.log absolute path
     */

    /**
     * @typedef {Object} LogsReaderResponse
     * @property {boolean} success - Operation status
     * @property {string} message - Error or fail message
     * @property {string} content - Log content snippet
     * @property {number} total_lines - Total lines in the file
     * @property {number} start_line - Index of the first line in content
     * @property {boolean} has_more - Is there more content to load upwards
     */

    window.LogsReader = Class.create({
        /**
         * @param {LogsReaderOptions} options
         */
        initialize: function(options) {
            /** @type {LogsReaderOptions} */
            this.options = options;
            this.currentLogFile = '';
            this.firstLoadedLine = 0;

            this.initElements();
            this.bindEvents();
            this.observeVisibility();
        },

        initElements: function() {
            this.textarea = $('#log_textarea');
            this.lineNumbers = $('#line_numbers');
            this.fileNameLabel = $('#current_file_name');
            this.loadMoreBtn = $('#load_more_btn');
            this.loadMoreContainer = $('#load_more_container');
            this.refreshBtn = $('#refresh_content')
            this.mainContainer = document.getElementById('logs_reader_main_container');
        },

        bindEvents: function() {
            const self = this;

            $(window).on('keydown', function(e) {
                if (e.key === 'F11') {
                    e.preventDefault();
                    self.toggleFullscreen();
                }
            });

            this.textarea.on('scroll', () => {
                this.lineNumbers.scrollTop(this.textarea.scrollTop());
            });

            this.loadMoreBtn.on('click', () => {
                const nextStart = Math.max(0, this.firstLoadedLine - this.options.pageSize);
                this.loadFileContent(this.currentLogFile, nextStart, true);
            });

            this.refreshBtn.on('click', () => {
                if (this.currentLogFile) {
                    this.loadFileContent(this.currentLogFile);
                }
            });
        },

        /**
         * Lazy load log file content
         */
        observeVisibility: function() {
            if (this.isInitialized || !this.mainContainer) {
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !this.isInitialized) {
                        this.isInitialized = true;
                        this.currentLogFile = this.options.defaultLogFile;
                        this.loadFileContent(this.currentLogFile);
                        observer.disconnect();
                    }
                });
            }, { threshold: 0.1 });

            observer.observe(this.mainContainer);
        },

        toggleFullscreen: function() {
            if (!document.fullscreenElement) {
                if (this.mainContainer.requestFullscreen) {
                    this.mainContainer.requestFullscreen();
                } else if (this.mainContainer.webkitRequestFullscreen) {
                    this.mainContainer.webkitRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        },

        /**
         * @param {string} file
         * @param {number|null} startLine
         * @param {boolean} appendTop
         */
        loadFileContent: function(file, startLine = null, appendTop = false) {
            if (!appendTop) {
                this.textarea.val($t('Loading...')).addClass('loading');
                this.lineNumbers.text('');
            }

            this.loadMoreBtn.prop('disabled', true);

            $.ajax({
                url: this.options.getFileContentUrl,
                type: 'GET',
                data: { file: file, startLine: startLine, isAjax: true },
                dataType: 'json',
                success: this.onLoadSuccess.bind(this, file, appendTop),
                error: this.onLoadError.bind(this),
                complete: this.onLoadComplete.bind(this, file)
            });
        },

        /**
         * @param {string} file
         * @param {boolean} appendTop
         * @param {LogsReaderResponse} res
         */
        onLoadSuccess: function(file, appendTop, res) {
            if (!res.success) {
                this.showError(res.message || $t('Unknown error occurred.'));
                return;
            }

            const lineData = this.prepareLineNumbers(res.content, res.start_line);

            if (appendTop) {
                this.renderContentAtTop(res, file, lineData);
            } else {
                this.renderContentFresh(res, file, lineData);
            }

            res.has_more ? this.loadMoreContainer.show() : this.loadMoreContainer.hide();
        },

        onLoadError: function(xhr, status, error) {
            this.showError($t('Failed to load file content. Server returned: ') + error);
        },

        /**
         * @param {string} file
         */
        onLoadComplete: function(file) {
            this.textarea.removeClass('loading');
            this.loadMoreBtn.prop('disabled', false);
            this.currentLogFile = file;
        },

        /**
         * @param {string} content
         * @param {number} start
         * @returns {{nums: string, count: number}}
         */
        prepareLineNumbers: function(content, start) {
            let nums = '';
            const linesArray = content.split('\n');
            if (linesArray[linesArray.length - 1] === "" && content.endsWith('\n')) {
                linesArray.pop();
            }

            for (let i = 0; i < linesArray.length; i++) {
                nums += (start + i + 1) + '\n';
            }

            return {
                nums: nums,
                count: linesArray.length
            };
        },

        /**
         * @param {LogsReaderResponse} res
         * @param {string} file
         * @param {Object} lineData
         */
        renderContentAtTop: function(res, file, lineData) {
            const oldScrollHeight = this.textarea[0].scrollHeight;
            const oldScrollTop = this.textarea.scrollTop();

            this.textarea.val(res.content + this.textarea.val());
            this.lineNumbers.prepend(lineData.nums);

            this.textarea.scrollTop(this.textarea[0].scrollHeight - oldScrollHeight + oldScrollTop);
            this.firstLoadedLine = res.start_line;
            this.updateFileLabel(res, file);
        },

        /**
         * @param {LogsReaderResponse} res
         * @param {string} file
         * @param {Object} lineData
         */
        renderContentFresh: function(res, file, lineData) {
            this.textarea.val(res.content);
            this.lineNumbers.text(lineData.nums);

            setTimeout(() => {
                this.textarea.scrollTop(this.textarea[0].scrollHeight);
                this.lineNumbers.scrollTop(this.textarea[0].scrollHeight);
            }, 10);

            this.firstLoadedLine = res.start_line;
            this.updateFileLabel(res, file);
        },

        /**
         * @param {LogsReaderResponse} res
         * @param {string} file
         */
        updateFileLabel: function (res, file) {
            const linesArray = this.textarea.val().split('\n');
            let count = linesArray.length;
            if (this.textarea.val().endsWith('\n')) {
                count--;
            }

            const endLine = Math.min(this.firstLoadedLine + count, res.total_lines);
            const labelText = file + ' (' + $t('lines') + ' ' + (this.firstLoadedLine + 1) + '-' +
                    endLine + ' ' + $t('of') + ' ' + (res.total_lines) + ')';

            this.fileNameLabel.text(labelText);
        },

        /**
         * @param {string} message
         */
        showError: function(message) {
            this.textarea.val($t('Error: ') + message);
            this.lineNumbers.text('!');
            this.loadMoreContainer.hide();
        }
    });

    return function(options) {
        new window.LogsReader(options);
    };
});
