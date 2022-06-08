(function ($) {

    var DomainAgeCheckerManager = {
        init: function () {
            this.cacheDom();
            this.bind();
        },

        cacheDom: function () {
            this.$domainCheckerWrapper = $('.domain-age-checker-wrapper');
            this.searchBtn  = this.$domainCheckerWrapper.find('#domain-submit');
        },

        bind: function () {
            this.searchBtn.on('click', this.xhr);
        },

        xhr: function (e) {
            e.preventDefault()

            var $this = $(this),
                urlQueryElement = $('textarea[name=domains]');

            // Check input validation
            if (!urlQueryElement[0].checkValidity()) {
                urlQueryElement.siblings('.error').show()
                $this.prop('disabled', false);
                return false;
            } else {
                urlQueryElement.siblings('.error').hide()
            }

            $this.prop('disabled', true);

            $.ajax({
                url: domain_age_checker_tool_data.ajaxurl,
                type: 'POST',
                data: {
                    security: domain_age_checker_tool_data.nonce,
                    action: 'domain_age_checker_xhr_action',
                    domains: urlQueryElement.val()
                },
                success: function (response) {
                    DomainAgeCheckerManager.$domainCheckerWrapper.find('.domain-age-wrapper').html(
                        response.html
                    )
                    $this.prop('disabled', false);
                }
            });
        }
    }
    DomainAgeCheckerManager.init();
}) (jQuery);