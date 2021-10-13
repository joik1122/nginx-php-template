<?php

    namespace Tracing;

    use Zipkin\Annotation;
    use Zipkin\Endpoint;
    use Zipkin\Samplers\BinarySampler;
    use Zipkin\TracingBuilder;
    use Zipkin\Reporters\Http;
    use Zipkin\Propagation\ServerHeaders;
    use Zipkin\Kind;

    class Tracer
    {
        /**
         * @var Singleton reference to singleton instance
         */
        private static $instance;
        
        private $endpoint;
        private $reporter;
        private $sampler;
        private $tracing;
        private $tracer;
        private $extracted;
        private $fullSpan;
        private $spanStack; //Child Span 스택
        private $enabled; //최초 활성화 여부

        /**
         * gets the instance via lazy initialization (created on first usage)
         *
         * @return self
         */
        public static function getInstance()
        {
            if (null === static::$instance) {
                static::$instance = new static;
            }
    
            return static::$instance;
        }
    
        /**
         * is not allowed to call from outside: private!
         *
         */
        private function __construct()
        {
            $this->spanStack = [];
            $this->enabled = false;
        }

        public function enable() 
        {
            // 재실행 방지
            if ($this->enabled) return;
            
            // 엔드포인트 정의
            $this->endpoint = Endpoint::create($_SERVER["PHP_SELF"]);
        
            // Jaeger 엔드포인트 정의
            $this->reporter = new Http(['endpoint_url' => 'http://jaeger-collector.tracing:9411/api/v2/spans']);
            $this->sampler = BinarySampler::createAsAlwaysSample();
            $this->tracing = TracingBuilder::create()
                ->havingLocalEndpoint($this->endpoint)
                ->havingSampler($this->sampler)
                ->havingReporter($this->reporter)
                ->build();
        
            // Request 헤더로부터 오픈트레이싱 정보를 수집하고 전파합니다
            $extractor = $this->tracing->getPropagation()->getExtractor(new ServerHeaders);
            $this->extracted = $extractor($_SERVER);
            $this->tracer = $this->tracing->getTracer();

            // Request에 대한 풀 스팬 생성
            $this->fullSpan = $this->tracer->nextSpan($this->extracted);
            $this->fullSpan->start();
            $this->fullSpan->setKind(Kind\SERVER);
            $this->fullSpan->setName('Full Span');

            /* 응답 전 자동으로 트레이서를 플러쉬합니다. */
            register_shutdown_function(function () {
                //if(!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'wb'));
                //fwrite(STDOUT, "호출 확인");
                while($this->spanPop());  // 종료되지 않은 스펜이 있다면 모두 종료
                $this->fullSpan->finish();
                $this->tracer->flush();
            });

            $this->enabled = true;
        }

        public function tagCurrentSpan($tagKey, $tagVal) {
            if($this->enabled === false) return;
            
            if(empty($this->spanStack)){
                $currentSpan = $this->fullSpan;
            } else {
                $currentSpan = end($this->spanStack);
            }
            
			$currentSpan->tag($tagKey, $tagVal);
        }

        public function spanPush($spanName)
        {
            if($this->enabled === false) return;

            if(empty($this->spanStack)){
                $childSpan = $this->tracer->newChild($this->fullSpan->getContext());
            } else {
                $childSpan = $this->tracer->newChild(end($this->spanStack)->getContext());
            }
            
            array_push($this->spanStack, $childSpan);
            $childSpan->start();
            $childSpan->setKind(Kind\SERVER);
            $childSpan->setName($spanName);
        }

        // 가장 마지막 Span 종료처리 및 리턴
        public function spanPop()
        {
            $lastSpan = array_pop($this->spanStack);
            if($lastSpan) $lastSpan->finish();
            return $lastSpan;
        }
    
        /**
         * prevent the instance from being cloned
         *
         * @return void
         */
        private function __clone()
        {
        }
    
        /**
         * prevent from being unserialized
         *
         * @return void
         */
        private function __wakeup()
        {
        }

        public function printStack() {
            print_r($this->spanStack);
        }
    }
?>