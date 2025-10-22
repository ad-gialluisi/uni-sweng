DOCKER_IMAGE := sweng-projfiles

INTERACTIVE := true
SHARED := true
SHARED_MATERIALS := true
GUI_PARAMS := -e DISPLAY=$(DISPLAY) -v /tmp/.X11-unix:/tmp/.X11-unix


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

ifeq ($(SHARED_MATERIALS), true)
	VOL_PARAMS_MATERIALS := -v ../Materiali:/root/materials
else
	VOL_PARAMS_MATERIALS :=
endif



all: stop rm build run
	@true

build:
	docker build --network host -t $(DOCKER_IMAGE) .

run:
	xhost +local:docker
	docker run --name $(DOCKER_IMAGE) $(GUI_PARAMS) $(VOL_PARAMS) $(VOL_PARAMS_MATERIALS) $(RUN_PARAMS) $(DOCKER_IMAGE)
	xhost -local:docker

stop:
	docker stop $(DOCKER_IMAGE) || true

rm:
	docker rm $(DOCKER_IMAGE) || true