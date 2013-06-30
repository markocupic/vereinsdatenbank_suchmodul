/**
 *
 * mini mediabox
 */
mmbOpen = function (content) {
    if (!document.id(content)) {
        return;
    }
    // fade in the overlay
    var overlay = new Element('div', {id:'mmbOverlay'});
    overlay.inject(document.body);
    overlay.fade(0.6);
    overlay.addEvent('click', function () {
        mmbClose();
    });
    window.addEvent('keydown', function (event) {
        if (event.key == 'esc') {
            mmbClose();
        }
    });
    // inject centerBox
    var centerBox = new Element('<div>', {'id':'mmbCenter'});
    centerBox.setStyles({
        'visibility':'hidden',
        'position':'absolute'
    });
    centerBox.inject(document.body, 'top');
    // inject contentBox
    var contentBox = document.id(content).clone();
    contentBox.setProperty('id', 'mmbContent');
    contentBox.setStyle('display', 'block');
    contentBox.inject(centerBox, 'top');

    // Set Width of the centerBox
    centerBox.setStyle('width', contentBox.offsetWidth);
    var bottomBox = new Element('<div>', {'id':'mmbBottom'});
    bottomBox.inject(contentBox, 'after');
    bottomBox.setStyles({
        'width':contentBox.offsetWidth,
        'height':'40px'
    });
    // Creating the close link
    var closeLink = new Element('a', {
        href:'javascript:void(0)',
        id:'mmbCloseLink',
        html:'x',
        styles:{
            'position':'absolute',
            'font-size':'20px',
            'font-weight':'bold',
            'line-height':'28px',
            'right':'15px'
        },
        events:{
            click:function () {
                mmbClose();
            }
        }
    });
    closeLink.inject(bottomBox);

    var top = window.getScrollTop() + 20;
    var left = window.getScrollLeft() + (window.getWidth() / 2) - (centerBox.offsetWidth / 2);
    centerBox.style.top = top + 'px';
    centerBox.style.left = left + 'px';
    centerBox.fade(1);
}

mmbClose = function () {
    var elements = ['mmbCenter', 'mmbOverlay'];
    elements.each(function (el) {
        if (document.id(el)) {
            document.id(el).destroy();
        }
    });
}