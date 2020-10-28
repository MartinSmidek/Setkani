<?php
# no enum --> abstract
abstract class Click {
    const EDIT = "opravit";
    const APPEND = "pridat";
    const HIDE = "skryt";
    const DEL = "zrusit";
    const ADD = "vytvorit";
}

interface ContextMenuBuilder {
    /**
     * @param $callback string - callback ezer func name
     * @param $title string - action title
     * @param $type string - object type we clicked on (clanek, foto..)
     * @param $arg1 int - arg1 passed to the Click method
     * @param $arg2 int - arg2 passed to the Click method
     * @return self - pattern
     */
    function new_callback($callback, $title, $type, $arg1, $arg2, $separator=false);

    function edit($title, $type, $arg1, $arg2, $separator=false);
    function add($title, $type, $arg1, $separator=false);
    function hide($title, $type, $arg1, $arg2, $separator=false);
    function delete($title, $type, $arg1, $arg2, $separator=false);
    function create($title, $type, $arg1, $arg2, $separator=false);

    /**
     * @param $arg string - Ezer.fce.oncontextmenu id for element beying edited, or null
     * @return string - on context menu action string for html "oncontextmenu" attribute
     */
    function html($arg);
}

class DummyBuilder implements ContextMenuBuilder {

    function new_callback($callback, $title, $type, $arg1, $arg2, $separator = false)
    {
        return $this;
    }

    function edit($title, $type, $arg1, $arg2, $separator = false)
    {
        return $this;
    }

    function add($title, $type, $arg1, $separator = false)
    {
        return $this;
    }

    function hide($title, $type, $arg1, $arg2, $separator = false)
    {
        return $this;
    }

    function delete($title, $type, $arg1, $arg2, $separator = false)
    {
        return $this;
    }

    function create($title, $type, $arg1, $arg2, $separator = false)
    {
        return $this;
    }

    function html($arg)
    {
        return "";
    }
}


class CMSBuilder implements ContextMenuBuilder {
    private $result = "oncontextmenu=\"Ezer.fce.contextmenu([";

    function new_callback($callback, $title, $type, $arg1, $arg2, $separator = false)
    {
        $sep = $separator ? "-" : "";
        $arg1 = $arg1 ? ", '$arg1'" : "";
        $arg2 = $arg2 ? ", '$arg2'" : "";
        $this->result .= "\n['$sep$title', function(el) { $callback('$type'$arg1$arg2); }],";
        return $this;
    }

    function edit($title, $type, $arg1, $arg2, $separator = false)
    {
        return $this->new_callback("opravit", $title, $type, $arg1, $arg2, $separator);
    }

    function add($title, $type, $arg1, $separator = false)
    {
        return $this->new_callback("pridat", $title, $type, $arg1, null, $separator);
    }

    function hide($title, $type, $arg1, $arg2, $separator = false)
    {
        return $this->new_callback("skryt", $title, $type, $arg1, $arg2, $separator);
    }

    function delete($title, $type, $arg1, $arg2, $separator = false)
    {
        return $this->new_callback("zrusit", $title, $type, $arg1, $arg2, $separator);
    }

    function create($title, $type, $arg1, $arg2, $separator = false)
    {
        return $this->new_callback("vytvorit", $title, $type, $arg1, $arg2, $separator);
    }

    /**
     * @inheritDoc
     */
    function html($arg)
    {
        $arg = $arg ? ", '$arg'" : "";
        return substr($this->result, 0, -1) . "\n],arguments[0] $arg);";
    }
}


# factory builder
class OnCtxClick {
    private static $builder;

    public static function builder($CMS) {
        //todo works when login?
        if (OnCtxClick::$builder == null) {
            OnCtxClick::$builder = $CMS ? new CMSBuilder() : new DummyBuilder();
        }
        return OnCtxClick::$builder;
    }
}