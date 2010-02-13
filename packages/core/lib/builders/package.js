

function dump(obj) { print(require('test/jsdump').jsDump.parse(obj)) };

var BUILDER = require("builder", "http://registry.pinf.org/cadorn.org/github/pinf/packages/common/");
var ARGS = require("args");


var Builder = exports.Builder = function(pkg, options) {
    if (!(this instanceof exports.Builder))
        return new exports.Builder(pkg, options);
    this.construct(pkg, options);
}

Builder.prototype = BUILDER.Builder();



Builder.prototype.build = function(targetPackage, buildOptions) {
    
    
    print("pkg: "+this.pkg.getPath());
    
    print("targetPackage: " + targetPackage.getPath());
    print("targetPackage(build path): " + targetPackage.getBuildPath());
    

print(" build modular PHP pakcge!!!!");
    
    var sourcePath = targetPackage.getPath(),
        buildPath = targetPackage.getBuildPath().join("raw"),
        fromPath, toPath;

    [
        "lib",
        "tests",
        "vhosts",   // for now
        "applications"  // for now
    ].forEach(function(item) {
        fromPath = sourcePath.join(item);
        toPath = buildPath.join(item);
        toPath.dirname().mkdirs();
        fromPath.symlink(toPath);
    })
}
