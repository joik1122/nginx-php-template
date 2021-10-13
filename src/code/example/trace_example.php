<?php

    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/autoload.php';

    use Tracing\Tracer;

    $tracer = Tracer::getInstance();
    $tracer->enable();
    sleep(1);

    $tracer->spanPush("A Span");
        usleep(200000); //200ms
    $tracer->spanPop();

    $tracer->spanPush("B Span");
        usleep(100000); //200ms
    
        $tracer->spanPush("B-1 Span");
            usleep(220000); //200ms
        $tracer->spanPop(); //close B-1 Span
        $tracer->spanPush("B-2 Span");
            usleep(174000); //200ms
        $tracer->spanPop(); //close B-1 Span
    $tracer->spanPop(); //B Span Close

?>