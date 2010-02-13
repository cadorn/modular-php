

function dump(obj) { print(require('test/jsdump').jsDump.parse(obj)) };

var LOCATOR = require("package/locator", "http://registry.pinf.org/cadorn.org/github/pinf/packages/common/");
var TESTER = require("tester", "http://registry.pinf.org/cadorn.org/github/pinf/packages/common/");
var UTIL = require("util");
var OS = require("os");
var TERM = require("term");

var Tester = exports.Tester = function(pkg, options) {
    if (!(this instanceof exports.Tester))
        return new exports.Tester(pkg, options);
    this.construct(pkg, options);
}

Tester.prototype = TESTER.Tester();


Tester.prototype.test = function(targetPackage, testOptions) {

    var phpunitPath = this.util.getCommandPath(targetPackage, "phpunit", testOptions.platform);
    
    var command = phpunitPath.valueOf() + " --colors " +
        targetPackage.getBuildPath().join("raw", "tests") +
        ((UTIL.len(testOptions.args)>0)?"/"+testOptions.args[0]:"");

    TERM.stream.print("\0cyan(Running: "+command+"\0)");
    TERM.stream.print("\0cyan(--------------------------------------------------\0)");

    OS.system(command);

    TERM.stream.print("\n\0cyan(--------------------------------------------------\0)");

}
