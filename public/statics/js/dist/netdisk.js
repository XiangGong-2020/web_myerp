$(function() {
    $('#elfinder').elfinder({
        // 后端连接器地址
        url: '/Base/Netdisk/connector',
        // 界面语言
        lang: 'zh_CN',
        // 携带 CSRF Token（解决 CI4 全局 CSRF 拦截，必加）
        requestData: {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        // 默认打开路径
        startPath: '/',
        debug: !1, // array or boolean, debug : ['error', 'warning', 'event-destroy']
        useBrowserHistory: !1,
        allowShortcuts: !1,
        // 分片上传
        uploadMaxChunk: 5,
        uploadMaxChunkSize: 10485760, // 10M，分片上传最大每片大小
        uiOptions: {
            toolbar: [
                ['back', 'forward', 'up', 'reload'],
                ['mkdir', 'mkfile', 'upload'],
                ['open', 'download'],
                ['undo', 'redo'],
                ['copy', 'cut', 'paste'],
                ['rm', 'empty'],
                ['duplicate', 'rename', 'edit', 'resize'],
                ['quicklook', 'info'],
                ['extract', 'archive'],
                ['view', 'sort'],
                ['search']
            ],
        },
        contextmenu: {
            // navbarfolder menu
            navbar: ['open', 'download', '|', 'upload', 'mkdir', '|', 'copy', 'cut', 'paste', 'duplicate', '|', 'rm', 'empty', '|', 'places', 'info'],
            // current directory menu
            cwd: ['undo', 'redo', '|', 'back', 'up', 'reload', '|', 'upload', 'mkdir', 'mkfile', 'paste', '|', 'rm', 'empty', '|', 'view', 'sort', '|', 'places', 'info'],
            // current directory file menu
            files: ['open', 'download', 'quicklook', '|', 'upload', 'mkdir', '|', 'copy', 'cut', 'paste', 'duplicate', '|', 'rm', 'empty', '|', 'rename', 'edit', 'resize', '|', 'archive', 'extract', '|', 'places', 'info']
        },
        commandsOptions: {
            edit: {
                extraOptions: {
                    // set API key to enable Creative Cloud image editor
                    // see https://console.adobe.io/
                    creativeCloudApiKey: '',
                    // browsing manager URL for CKEditor, TinyMCE
                    // uses self location with the empty value
                    managerUrl: ''
                }
            },
            quicklook: {
                // to enable CAD-Files and 3D-Models preview with sharecad.org
                sharecadMimes: ['image/vnd.dwg', 'image/vnd.dxf', 'model/vnd.dwf', 'application/vnd.hp-hpgl', 'application/plt', 'application/step', 'model/iges', 'application/vnd.ms-pki.stl', 'application/sat', 'image/cgm', 'application/x-msmetafile'],
                // to enable preview with Google Docs Viewer
                googleDocsMimes: ['application/pdf', 'image/tiff', 'application/vnd.ms-office', 'application/msword', 'application/vnd.ms-word', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/postscript', 'application/rtf'],
                // to enable preview with Microsoft Office Online Viewer
                // these MIME types override "googleDocsMimes"
                officeOnlineMimes: ['application/vnd.ms-office', 'application/msword', 'application/vnd.ms-word', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.presentation']
            }
        },
        bootCallback: function(fm, extraObj) {
            fm.bind('init', function() {
            });
            var title = document.title;
            fm.bind('open', function() {
                var path = '',
                    cwd = fm.cwd();
                if (cwd) {
                    path = fm.path(cwd.hash) || null;
                }
                document.title = path ? path + ':' + title : title;
            }).bind('destroy', function() {
                document.title = title;
            });
        },
        // 文件选中回调（用于弹窗选文件回传给父页面）
        getFileCallback: function(file) {
            // 示例：将选中的文件 URL 和名称回传给父窗口
            if (window.parent && window.parent.onFileSelected) {
                window.parent.onFileSelected(file.url, file.name);
                window.parent.close();
            }
        }
    });
});