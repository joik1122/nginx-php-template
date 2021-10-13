## template-nginx-php
> Creator: Jonas
> Date: 2020/11/04

## Description
* template-nginx-php 쿠버네티스 repository
* 설정 수정 방법 -> src/config에서 PHP와 nginx 설정 수정할 수 있습니다.

## 해당 템플릿을 이용하여 개발하고자 할 때
* /k8s 하위의 Chart.yaml, values.yaml들을 수정해주세요.
* prod.values.yaml의 external.domain을 수정해주세요.
* skaffold.yaml에 예시로 되어있는 벨류들을 변경해주세요.
* 필요에 따라 Dockerfile 수정이 필요할 수 있으니 Dockerfile의 내용도 확인해주세요.
* 기본적인 환경 설정 및 PHP 설정들이 되어있으나, 필요 없거나 필요한 것들은 커스텀하여 개발하시면 됩니다.
* /src/config/etc/supervisor.d를 꼭 확인하시고, 불필요한 프로세스는 **주석처리하여** 비활성화 해주세요.
* /src/config/periodic/ 하위 경로에 디렉토리별로 실행파일을 넣어두면 cron run-parts로 실행됩니다. 간단히 주기적인 실행이 필요한 스크립트 등은 해당 경로에 넣으시면 되겠습니다.
* 실제 배포전, k8s/templates/deployment.yaml의 리소스 설정이 상당히 중요합니다. 

## Tracing에 대하여
* 리얼패킹 클라우드 환경에서 Jaeger Tracing을 이용합니다.
* 현재 PHP에 대해 Jaeger Client 지원이 미흡하여, Zipkin PHP Client를 Jaeger에 연동하여 사용합니다.
* 사용법에 대한 예제는 /src/code/example/trace_example.php를 확인해주세요.