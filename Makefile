.PHONY: upgrade

run:
	docker-compose up --build

simulate:
	ngrok http 3300