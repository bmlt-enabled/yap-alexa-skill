.PHONY: upgrade

run:
    composer install
	docker-compose up --build

simulate:
	ngrok http 3300
