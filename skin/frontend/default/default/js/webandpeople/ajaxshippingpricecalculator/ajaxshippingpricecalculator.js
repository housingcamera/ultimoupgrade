function wpRemoveValidatorMarks()
{
    if (calculatorValidateFields == null) return;
    calculatorValidateFields = {};
    // --- save validator mark and remove from field ---
    var elements = Form.getElements('shipping-calculator-form');
    for (var i=0; i<elements.length; i++) {
        if (elements[i].hasClassName('validate-select')) {
            elements[i].removeClassName('validate-select')
            calculatorValidateFields[elements[i].id] = 'validate-select';
        }
        if (elements[i].hasClassName('required-entry')){
            elements[i].removeClassName('required-entry')
            calculatorValidateFields[elements[i].id] = 'required-entry';
        }
    }
}

function wpRestoreValidatorMarks()
{
    if (calculatorValidateFields == null) return;
    // --- set saved validator marks ---
    $H(calculatorValidateFields).each(function(pair) {
        $(pair.key).addClassName(pair.value);
    });
}

function wpCalculateProductShipping(calculatorUrl)
{
    wpRestoreValidatorMarks();
    var items = $$(['.shipping-calculator-form input', '.shipping-calculator-form select', '#product_addtocart_form input', '#product_addtocart_form select']);
    var validateItems = $$(['.shipping-calculator-form input', '.shipping-calculator-form select']);
    if (!validateItems.map(Validation.validate).all()){
        wpRemoveValidatorMarks();
        return;
    }
    var parameters = Form.serializeElements(items, true);
    $('shipping-calculator-loading-message').show();
    $('shipping-calculator-results').hide();
    $('shipping-calculator-button').hide();
    new Ajax.Updater('shipping-calculator-results', calculatorUrl, {
        method: 'post',
        parameters: parameters,
        onComplete: function() {
            $('shipping-calculator-loading-message').hide();
            $('shipping-calculator-results').show();
            $('shipping-calculator-button').show();
        }
    });
}

WP_WinPopup = Class.create({
    id       : false,
    title    : false,
    popup    : false,
    popupObj : false,

    initialize:function(data) {
        this.id = data.id;
        this.title = data.title;
        winPopup = $(this.id);
        if (!winPopup){
            popupHtml = '' +
            '<div id="' + this.id + '" class="wp-shipping-calculator-popup opc">' +
                '<div class="section active">' +
                    '<div class="step-title">' +
                        '<h2>' + this.title + '</h2>' +
                        '<span id="' + this.id + '_btn_close" class="number">X</span>' +
                    '</div>' +
                    '<div class="step a-item" id="' + this.id + '_content_html">' +
                        data.html +
                    '</div>' +
                '</div>' +
            '</div>';

            $$('body')[0].insert(popupHtml);

            var winPopup = $(this.id);
        }
        winPopup.style.position = 'absolute';
        winPopup.style.display = 'block';
        if (typeof data.width != 'undefined') winPopup.style.width = data.width + 'px';
        if (typeof data.height != 'undefined') winPopup.style.height = data.height + 'px';
        this.popup = winPopup;
        this.setPosition();
        $(this.id + '_btn_close').onclick = function(){ this.close(); }.bind(this);
        var overlay = this.showOverlay();
        overlay.onclick = function(){ this.close(); }.bind(this);
    },

    open: function() {
        var overlay = this.showOverlay();
        overlay.onclick = function(){ this.close(); }.bind(this);
        this.setPosition();
        this.popup.show();
    },

    close: function() {
        this.hideOverlay();

        this.popup.hide();
    },

    setPosition: function() {
        this.popup.style.display = 'block';
        var left    = this.popup.offsetWidth / 2;
        var top     = this.getClientHeight() / 2 + this.getScrollY() - 170;
        this.popup.style.left = '50%';
        this.popup.style.marginLeft = '-' + left + 'px';
        this.popup.style.top  = top +'px';
    },

    setContent: function(html) {
        $(this.id + '_content_html').update(html);
    },

    showOverlay:function() {
        var overlay = $('shipping-calculator-x-overlay');
        if (overlay) {
            overlay.show();
        } else {
            overlay = document.createElement('div');
            overlay.id = 'shipping-calculator-x-overlay';
            overlay.className = 'shipping-calculator-overlay';
            overlay.style.width    = this.getPageWidth() + 'px';
            overlay.style.height   = this.getPageHeight() + 'px';
            document.body.appendChild(overlay);
        }
        return overlay;
    },

    hideOverlay:function() {
        var overlay = $('shipping-calculator-x-overlay');
        if (overlay) overlay.hide();
    },

    getClientHeight: function() {
        return this.filterResults (
            window.innerHeight ? window.innerHeight : 0,
            document.documentElement ? document.documentElement.clientHeight : 0,
            document.body ? document.body.clientHeight : 0
        );
    },

    filterResults: function(n_win, n_docel, n_body) {
        var n_result = n_win ? n_win : 0;
        if (n_docel && (!n_result || (n_result > n_docel))) n_result = n_docel;
        return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
    },

    getScrollY: function() {
        scrollY = 0;
        if (typeof window.pageYOffset == 'number') {
            scrollY = window.pageYOffset;
        } else if (document.documentElement && document.documentElement.scrollTop) {
            scrollY = document.documentElement.scrollTop;
        } else if (document.body && document.body.scrollTop) {
            scrollY = document.body.scrollTop;
        } else if (window.scrollY) {
            scrollY = window.scrollY;
        }
        return scrollY;
    },

    getPageHeight: function() {
        var D = document;
        return Math.max(
            D.body.scrollHeight, D.documentElement.scrollHeight,
            D.body.offsetHeight, D.documentElement.offsetHeight,
            D.body.clientHeight, D.documentElement.clientHeight
        );
    },

    getPageWidth: function() {
        var D = document;
        return Math.max(
            D.body.scrollWidth, D.documentElement.scrollWidth,
            D.body.offsetWidth, D.documentElement.offsetWidth,
            D.body.clientWidth, D.documentElement.clientWidth
        );
    }
});
