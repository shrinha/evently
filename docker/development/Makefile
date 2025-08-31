# Define Docker Compose command
COMPOSE = docker compose -f docker-compose.dev.yml

# Start dev environment using the setup script (auto-detects OS)
dev: ## Run the full start-dev script (setup + containers + migrations)
ifeq ($(OS),Windows_NT)
	@echo "Running start-dev.ps1 on Windows..."
	powershell.exe -ExecutionPolicy Bypass -File ./start-dev.ps1
else
	@echo "Running start-dev.sh on Linux/Mac..."
	chmod +x ./start-dev.sh && ./start-dev.sh
endif

start: ## Start all containers in detached mode
	$(COMPOSE) up -d

stop: ## Stop all containers
	$(COMPOSE) down

restart: stop start ## Restart containers

down-v: ## Stop and remove containers + volumes (DB, Redis, MinIO reset)
	$(COMPOSE) down -v

logs: ## Tail logs from all containers
	$(COMPOSE) logs -f

ps: ## Show running containers
	$(COMPOSE) ps
