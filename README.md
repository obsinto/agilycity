# AgilyCity - Sistema de Gestão Municipal

![AgilyCity Logo](https://via.placeholder.com/200x50?text=AgilyCity)

AgilyCity é um sistema completo de gestão municipal desenvolvido para otimizar processos administrativos e proporcionar
maior transparência na gestão pública. O sistema oferece ferramentas para gerenciamento de secretarias, departamentos,
despesas, orçamentos e recursos educacionais.

## 🌟 Principais Funcionalidades

### Dashboard Personalizado

- **Dashboard do Prefeito**: Visão geral de todas as secretarias e indicadores municipais
- **Dashboard de Secretaria**: Gestão específica para cada secretaria municipal
- **Dashboard de Setor**: Visão operacional para líderes de departamentos e escolas

### Gestão de Despesas

- **Despesas Regulares**: Registro e acompanhamento de despesas pontuais
- **Despesas Fixas**: Automação de despesas recorrentes (salários, aluguéis, etc.)
- **Controle de Tipos de Despesa**: Categorização personalizada de gastos
- **Teto de Gastos**: Definição e monitoramento de limites orçamentários

### Gestão Educacional

- **Cadastro de Alunos**: Gerenciamento de matrículas nas escolas municipais
- **Análise Escolar**: Relatórios e estatísticas de matrículas por período
- **Controle de Merenda**: Sistema especializado para gestão da cantina central
- **Relatório de Cantina**: Distribuição proporcional de custos entre escolas

### Estrutura Administrativa

- **Gerenciamento de Secretarias**: Configuração e monitoramento de órgãos municipais
- **Gerenciamento de Departamentos**: Organização interna de cada secretaria
- **Associação de Líderes**: Atribuição de responsáveis para cada unidade
- **Sistema de Permissões**: Controle granular de acesso baseado em papéis

## 🚀 Tecnologias Utilizadas

- **Backend**: Laravel 11
- **Frontend**: Blade Templates, Alpine.js, Tailwind CSS
- **Banco de Dados**: MySQL
- **Containerização**: Docker

## 🔧 Pré-requisitos

- PHP 8.2+
- Composer 2.0+
- Node.js 18.0+ e NPM
- Docker e Docker Compose (recomendado)
- MySQL 8.0+

## 📋 Instalação

### Usando Docker (Recomendado)

1. Clone o repositório:

```bash
git clone https://github.com/seu-usuario/agilycity.git
cd agilycity
```

2. Configure o arquivo .env:

```bash
cp .env.example .env
# Edite o arquivo .env com suas configurações
```

3. Inicie os containers:

```bash
docker-compose up -d
```

4. Execute as migrações e seeders:

```bash
docker-compose exec app php artisan migrate --seed
```

### Instalação Manual

1. Clone o repositório:

```bash
git clone https://github.com/seu-usuario/agilycity.git
cd agilycity
```

2. Instale as dependências:

```bash
composer install
npm install
```

3. Configure o arquivo .env:

```bash
cp .env.example .env
# Edite o arquivo .env com suas configurações
php artisan key:generate
```

4. Execute as migrações e seeders:

```bash
php artisan migrate --seed
```

5. Compile os assets:

```bash
npm run build
```

6. Inicie o servidor:

```bash
php artisan serve
```

## 🔄 Funcionalidades Especiais

### Sistema de Despesas Fixas

O AgilyCity implementa um sistema sofisticado de despesas fixas com as seguintes características:

1. **Cadastro de Despesas Recorrentes**: Configure uma vez e o sistema gera lançamentos mensais automaticamente
2. **Histórico de Alterações**: Mantém registro de mudanças nos valores ao longo do tempo
3. **Automação por Agendamento**: Utiliza o Laravel Scheduler para gerar automaticamente os lançamentos no início de
   cada mês
4. **Datas de Vigência**: Possibilidade de definir períodos específicos para validade da despesa fixa

Para ativar o agendador, adicione a seguinte entrada ao seu crontab:

```bash
* * * * * cd /caminho-para-seu-projeto && php artisan schedule:run >> /dev/null 2>&1
```

### Sistema de Cantina Central

O módulo de gestão da cantina central permite:

1. **Registro de Despesas de Merenda**: Controle específico para gastos com alimentação escolar
2. **Distribuição Proporcional**: Cálculo automatizado de custos por aluno em cada escola
3. **Relatórios Mensais**: Visualização detalhada da distribuição de recursos entre escolas

## 👥 Estrutura de Usuários e Permissões

O sistema utiliza os seguintes perfis de usuário:

- **Prefeito (mayor)**: Acesso total ao sistema
- **Secretário (secretary)**: Acesso completo à sua secretaria
- **Secretário de Educação (education_secretary)**: Permissões especiais para gestão educacional
- **Líder de Setor (sector_leader)**: Gerencia um departamento específico
- **Líder de Escola (school_leader)**: Gerencia uma unidade escolar
- **Líder de Cantina (cantina_leader)**: Responsável pela cantina central

## 🛠️ Ambiente de Desenvolvimento

O projeto está configurado com:

- **Laravel Sail**: Ambiente Docker para desenvolvimento
- **Vite**: Compilação rápida de assets
- **Laravel Debugbar**: Ferramentas de depuração
- **Laravel Permission (Spatie)**: Sistema de controle de acesso baseado em papéis

## 📱 Responsividade

O sistema é totalmente responsivo, adaptando-se a diferentes tamanhos de tela:

- **Desktop**: Experiência completa com sidebar expansível
- **Tablet**: Interface adaptativa com menu colapsável
- **Mobile**: Menu mobile otimizado para telas pequenas

## 🧪 Testes

Execute os testes automatizados com:

```bash
php artisan test
```

## 📚 Documentação Adicional

- [Manual do Usuário](docs/user-manual.md)
- [Documentação da API](docs/api.md)
- [Guia de Contribuição](CONTRIBUTING.md)

## 📄 Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

## 👨‍💻 Contato

Para suporte ou mais informações, entre em contato pelo email: contato@agilycity.com
