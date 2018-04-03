define(function (require) {
    var customElem = require('customElement').create();
    var util = require('util');

    var attrList = ['allowfullscreen', 'allowtransparency', 'sandbox'];


    customElem.prototype.build = function () {
        var element = this.element;
        var postId = element.getAttribute('post-id') || '1';
        var bloghostElement = document.querySelector('meta[name=bloghost]');

        var height = element.getAttribute('height') || 'auto';
        var width = element.getAttribute('width') || '100%';

        var iframe = document.createElement('iframe');
        var bloghost = bloghostElement.getAttribute('content');
        var src = bloghost + '?mip&component=comment&id=' + postId;

        var onIframeMessageEvent = function (event) {
            var data = event.data;
            if (data.from !== 'zblogphp') return;
            switch (data.event) {
                case 'resize':
                if (height === 'auto') {
                    util.css(iframe, {
                        height: data.height
                    });
                }
            }
        }
        if (!bloghostElement) {
            console.error('您还未设置博客域名');
            return;
        }

        iframe.frameBorder = '0';
        iframe.scrolling = 'no';
        util.css(iframe, {
            width: width
        });
        if (height !== 'auto') {
            util.css(iframe, {
                height: height
            });
        }

        this.applyFillContent(iframe);
        iframe.src = src;

        this.expendAttr(attrList, iframe);
        element.appendChild(iframe);
        window.addEventListener('message', onIframeMessageEvent)
    };

    return customElem;
});
