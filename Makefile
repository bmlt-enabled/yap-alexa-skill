.PHONY: help run simulate build clean

VERSION := $(shell git describe --tags --always --dirty)
ZIP := build/yap-alexa-skill-$(VERSION).zip

help: ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

run: ## Install dependencies and start Docker
	composer install
	docker compose up --build

simulate: ## Start ngrok tunnel on port 3300
	ngrok http 3300

build: clean ## Build a deployable zip
	mkdir -p build
	composer install --no-dev --optimize-autoloader --no-interaction
	zip -rq $(ZIP) index.php functions.php handlers composer.json composer.lock vendor
	@echo "Created $(ZIP)"

clean: ## Remove build directory
	rm -rf build vendor
