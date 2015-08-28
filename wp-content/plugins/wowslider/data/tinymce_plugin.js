
(function(){
    tinymce.create('tinymce.plugins.wowslider', {
        init : function(ed, url){
            for (var i in tinymce_wowslider.sliders){
                tinymce_wowslider.last = tinymce_wowslider.sliders[i].id;
                break;
            }
            if (tinymce.majorVersion >= 4){
                if (tinymce_wowslider.last){
                    var menu = [];
                    for (var i in tinymce_wowslider.sliders){
                        menu.push({
                            text : tinymce_wowslider.sliders[i].name,
                            data : tinymce_wowslider.sliders[i].id
                        });
                    }
                    var insert = function(e){
                        var id = e.control.settings.data || tinymce_wowslider.last;
                        tinymce.execCommand('mceInsertContent', false, '[wowslider id="' + id + '"]');
                    };
                    ed.addButton('wowslider', {
                        type : 'splitbutton',
                        icon : 'wowslider',
                        menu : menu,
                        tooltip : tinymce_wowslider.title,
                        onclick : insert,
                        onselect : insert
                    });
                }
            } else {
                tinymce_wowslider.url = url;
                tinymce_wowslider.insert = function(){
                    var id = (this.v ? this.v : tinymce_wowslider.last);
                    tinymce.execCommand('mceInsertContent', false, '[wowslider id="' + id + '"]');
                };
            }
        },
        createControl : function(n, cm){
            switch (n){
                case 'wowslider':
                    var c = cm.createSplitButton('wowslider', {
                        title : tinymce_wowslider.title,
                        image : tinymce_wowslider.url + '/icon.png',
                        onclick : tinymce_wowslider.insert
                    });
                    c.onRenderMenu.add(function(c, m){
                        for (var i in tinymce_wowslider.sliders){
                            m.add({
                                v : tinymce_wowslider.sliders[i].id,
                                title : tinymce_wowslider.sliders[i].name,
                                onclick : tinymce_wowslider.insert
                            });
                        }
                    });
                    return c;
            }
            return null;
        },
    });
    tinymce.PluginManager.add('wowslider', tinymce.plugins.wowslider);
})();
