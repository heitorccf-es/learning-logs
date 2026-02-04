Aplicação Full-Stack para busca de receitas baseada em ingredientes disponíveis. Utiliza Laravel (API) para processamento e cache, Next.js para interface, consumindo a API pública TheMealDB.

## Tecnologias

- **Back-end:** Laravel 11, PHP 8.2+
- **Front-end:** Next.js 14 (App Router), Tailwind CSS
- **Infraestrutura:** Docker & Docker Compose
- **API Externa:** TheMealDB

## Rodando com Docker (Recomendado)

Pré-requisitos: Docker e Docker Compose instalados.

### 1. Clonar repositório

```bash
git clone https://github.com/heitorccf-es/learning-logs.git
```

```bash
cd learning-logs
```

### 2. Configurar variáveis de ambiente

```bash
cp laravel/.env.example laravel/.env
```

```bash
cp next/.env.local.example next/.env.local
```

Os arquivos `.env.example` contêm as variáveis necessárias. Ajuste os valores conforme seu ambiente.

### 3. Subir containers

```bash
docker-compose up -d --build
```

### 4. Configurar Laravel (primeira execução)

```bash
docker-compose exec app composer install
```

```bash
docker-compose exec app php artisan key:generate
```

```bash
docker-compose exec app php artisan migrate
```

Acesse: <http://localhost:3000>

## Capturas de Tela

<p align="center">
  <img src="imagens/Design sem nome.svg" alt="Tela Inicial" width="80%" />
</p>

<p align="center">
  <img src="imagens/Design sem nome (1).svg" alt="Busca de Ingredientes" width="80%" />
</p>

<p align="center">
  <img src="imagens/Design sem nome (2).svg" alt="PDF Gerado" width="80%" />
</p>
