.PHONY: help run simulate

help: ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

run: ## Install dependencies and start Docker
	composer install
	docker compose up --build

simulate: ## Start ngrok tunnel on port 3300
	ngrok http 3300
