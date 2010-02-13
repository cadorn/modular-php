
function dump(obj) { print(require('test/jsdump').jsDump.parse(obj)) };

var DESCRIPTOR = require("package/descriptor", "http://registry.pinf.org/cadorn.org/github/pinf/packages/common/");
var UTIL = require("util");
var FILE = require("file");


exports.normalizeUrl = function(context, uri)
{
    // only proceed if modular-php is calling
    if(context.builder.pkg.getUid()!="http://registry.pinf.org/cadorn.org/github/platforms/packages/php/packages/modular/") {
        return uri;
    }
    
    // only proceed if the URI matches one we should service
    var descriptor = DESCRIPTOR.PackageDescriptor(FILE.Path(module.path).dirname().join("../../package.json")),
        spec = descriptor.getImplementsForUri("http://registry.pinf.org/cadorn.org/github/pinf/@meta/routing/url/0.1.0").mappings;
    if(!UTIL.has(UTIL.keys(spec), uri.url)) {
        return uri;
    }

    var url = uri.scheme + ":" + uri.authorityRoot,
        parts = UTIL.copy(uri.directories);

    // hardcode the platform name into the route
    parts[3] = context.targetPackage.getName();

    url += "/" + parts.join("/") + "/" + uri.file;

    return url;
}

exports.getImplementation = function(context, uri)
{
    // TODO: move implementation detail upstream and use simple route plugin here
    return [
        "if(preg_match_all('/^\\/pinf\\/([^\\/]*)\\/@platforms\\/" + context.targetPackage.getName() + "\\/@uid\\/(.*?)\\/@revision\\/(.*?)\\/@direct\\/(.*)$/s', $_SERVER['REQUEST_URI'], $m)) {",
        "$file = '" + context.targetPackage.getPath().join("bin", "mp-test-direct-gateway") + "-' . ModularPHP_Bootstrap::GetOption('platformVariantName');",
        "$RESPONDER_ARGS = array('accessKey'=>$m[1][0], 'uid'=> $m[2][0], 'revision'=> $m[3][0], 'path' => $m[4][0]);",
        "header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');",
        "header('Status: 200');",
        "require_once($file);",
        "}"
    ].join("\n");
}
