# MUDANÇA: Usando Node 20 (Obrigatório para Next.js 16)
FROM node:20-alpine

WORKDIR /app

# Copia apenas package.json e package-lock.json primeiro (cache layer)
COPY package*.json ./

# Instala dependências
# MUDANÇA: Adicionei cache limpo para garantir
RUN npm ci --only=production=false && npm cache clean --force

# Copia o resto do código
COPY . .

# Define variáveis de ambiente
ENV NODE_ENV=development
ENV NEXT_TELEMETRY_DISABLED=1

EXPOSE 3000

CMD ["npm", "run", "dev"]
