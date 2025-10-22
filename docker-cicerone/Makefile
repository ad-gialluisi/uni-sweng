DOCKER_IMAGE := sweng

PORTS := -p 80:80
INTERACTIVE := false
SHARED := false



ifeq ($(INTERACTIVE), true)
	RUN_PARAMS := -ti
else
	RUN_PARAMS := -t
endif

ifeq ($(SHARED), true)
	VOL_PARAMS := -v /tmp:/shared
else
	VOL_PARAMS :=
endif




all: stop rm build run
	@true

build:
	docker build --network host -t $(DOCKER_IMAGE) .

run:
	docker run --name $(DOCKER_IMAGE) $(PORTS) $(VOL_PARAMS) $(RUN_PARAMS) $(DOCKER_IMAGE)

stop:
	docker stop $(DOCKER_IMAGE) || true

rm:
	docker rm $(DOCKER_IMAGE) || true