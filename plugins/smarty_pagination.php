<?
function smarty_pagination($params, &$smarty)
{
        $s = $params['start'];
        $t = $params['total'];
        $f = $params['function'];

        if ($t<=1) return;
        print $params['prepend'];
        if ($params['type']=='select')
        {
                print "<select onchange=\"$f(this.value)\">";
                for($i=1;$i<=$t;$i++)
                {
                    print "<option";
                        if ($i==$s) print " selected";
                        print ">$i</option>";
                }
                print "</select>";
        }
        else
        {
                $maxbox = intval($params['max_links']);
                if ($maxbox<=10) $maxbox=10;

            $sp=1;
                if ($t>$maxbox) $sp=$s-($maxbox/2);
                if ($sp<1) $sp=1;
                $ep=$sp+($maxbox-1);
                if ($ep>$t) $ep=$t;
            for($i=$sp;$i<=$ep;$i++)
                {
                    if ($i==$s){
                            //print "<li class='active'><span>$i <span class=sr-only>(current)</span></span></li>";
                            print "<a class='active'><span>$i </span></a>&nbsp;";
                    }
                    else {
                            //print "<li><a href=\"javascript:void($f($i))\">$i</a></li>";
                            print "<a href=\"javascript:void($f($i))\">$i</a>&nbsp;";
                    }
                        /*print "<li";
                        if ($i==$s) print " class=current";
                        print "><a href=\"javascript:void($f($i))\">$i</a></li>";*/
                }
                if ($ep<$t) print "...";
        }
        print $params['append'];
}



function smarty_pagination2($params, &$smarty)
{
        $s = $params['start'];
        $t = $params['total'];
        $f = $params['function'];

        if ($t<=1) return;
        print $params['prepend'];
        if ($params['type']=='select')
        {
                print "<select onchange=\"$f(this.value)\">";
                for($i=1;$i<=$t;$i++)
                {
                    print "<option";
                        if ($i==$s) print " selected";
                        print ">$i</option>";
                }
                print "</select>";
        }
        else
        {
                $maxbox = intval($params['max_links']);
                if ($maxbox<=10) $maxbox=10;

            $sp=1;
                if ($t>$maxbox) $sp=$s-($maxbox/2);
                if ($sp<1) $sp=1;
                $ep=$sp+($maxbox-1);
                if ($ep>$t) $ep=$t;
            for($i=$sp;$i<=$ep;$i++)
                {
                    if ($i==$s){
                            print "<li class='active'><span>$i <span class=sr-only>(current)</span></span></li>";
                            //print "<a class='active'><span>$i </span></a>&nbsp;";
                    }
                    else {
                            print "<li><a href=\"javascript:void($f($i))\">$i</a></li>";
                            //print "<a href=\"javascript:void($f($i))\">$i</a>&nbsp;";
                    }
                        /*print "<li";
                        if ($i==$s) print " class=current";
                        print "><a href=\"javascript:void($f($i))\">$i</a></li>";*/
                }
                if ($ep<$t) print "...";
        }
        print $params['append'];
}


// register the smarty function
_call_at('init', '$this->tpl->registerPlugin("function","pagination","smarty_pagination");');
_call_at('init', '$this->tpl->registerPlugin("function","pagination_faq","smarty_pagination2");');
?>