function rz_news_post_loadmore(options) {
    this.field_container = options.field_container;
    this.load_more_container = options.load_more_container;
    this.load_more_button = options.load_more_button;
    this.load_more_data_auto_append = this.load_more_data_auto_append;
    this.load_more_data = null;
    this.init();
}

rz_news_post_loadmore.prototype = {
    init: function() {
        var that = this;
        that.initLoadMoreButton(jQuery(sprintf('#%s', that.load_more_button)));
    },

    //loadMore
    loadMore: function(event) {
        var that = this;
        event.preventDefault();
        event.stopPropagation()

        //jQuery.blockUI({ message:'Processing'});

        var url_load_more = jQuery(sprintf("#%s", that.load_more_button)).attr('href');

        jQuery.ajax({
            type: 'GET',
            dataType: 'json',
            url: url_load_more
        })
            .done(function(data, textStatus, jqXHR) {
                that.setLoadMoreData(data.html);
                if(that.load_more_data_auto_append) {
                    jQuery(sprintf('#%s', that.field_container)).append(data.html);
                }
                jQuery(sprintf('#%s', that.load_more_container)).html(data.html_pager);
            })
            .fail(function(jqXHR, textStatus, errorThrown){
//                console.log(sprintf('[%s|loadMore] ajax fail', that.id));
//                console.log(sprintf('[%s|loadMore] error message', that.id));
            })
            .always(function(data) {
                that.initThumbnails( jQuery('.porthumb img'));
                that.initLoadMoreButton(jQuery(sprintf('#%s', that.load_more_button)));
                jQuery( document ).trigger( "rz:post_loadmore_custom_event" )
                //jQuery.unblockUI();
            });
        return;
    },

    initThumbnails: function(thumbContainer) {
        var that = this;
        thumbContainer.each(function() {
            $(this).animate({opacity:'1'},1000);
        });
    },

    initLoadMoreButton: function(button) {
        var that = this;
        button.click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            that.loadMore(event);
            return false;
        });
    },

    setLoadMoreData: function(data) {
        var that = this;
        that.load_more_data = data;
    },

    getLoadMoreData: function() {
        var that = this;
        return that.load_more_data;
    }
}
