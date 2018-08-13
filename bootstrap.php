<?php

# We use a global variable here to make sure the __destruct is only called at the end of the script's execution.
# Otherwise, it'd be called right after the execution of this file, which would delete all temp files.
$GLOBALS["d094efdc456faca4f987b6fc6e44c6c10a892765"] =  new Brunodebarros\FixPhpPostInput\FixPhpPostInput();
