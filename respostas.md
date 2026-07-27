# Perguntas Teóricas

## 1 API Resources no Laravel Explique: 

### Qual é o objetivo de utilizar API Resources?

#### Resposta:

API Resources podem ser úteis em diversos contextos no desenvolvimento de uma API.
As principais vantagens de utilizar um API resource são:

- Controlar payloads: Por mais que as models possam receber configurações de campos sensíveis que não são acessíveis normalmente, alguns fluxos ainda podem ter acesso a esses dados realizando uma query mais específica. Retornar essa model acarretaria no vazamento dos dados ao usuário. Utilizar uma resource evita que esse erro ocorra, fazendo com que a estrutura dos dados expostos seja controlada e padronizada.

- Consistência: Garante consistência nas respostas retornadas pela API. Todas as respostas retornadas que utilizam uma resource sempre retornarão o mesmo corpo. Com isso, dois fluxos diferentes que podem retornar o mesmo dado retornarão sempre o mesmo corpo.

- Desacoplamento: Alterar um campo se torna mais fácil, já que apenas a resource precisará ser ajustada.

- Evita poluição: Transformações de dados, campos derivados, etc. Podem ser feitos na resource, evitando poluição de models / services.

- Integrações externas: É possível ter várias resources para uma mesma model, para facilitar integrações com outros serviços, fazendo com que a partir de uma única model seja possível gerar respostas personalizadas para outros serviços.

### Em quais situações eles são úteis no desenvolvimento de APIs?

#### Resposta:

A partir dos objetivos mencionados, é possível extrair situações de utilidade de resources, como por exemplo: Controllers que retornam um mesmo model / entidade exposto em vários lugares que pode conter dados sensíveis. Quando se quer padronizar respostas para um certo objeto. Diferentes saídas para diferentes consumidores.

## 2 Organização de Validação em Laravel

### Explique as vantagens de utilizar classes específicas para validação de dados, em vez de realizar validações diretamente no controller. Considere aspectos como: Organização do código; Manutenção; Reutilização.

#### Resposta:

Utilizar classes específicas de validação (FormRequests) em vez de validar diretamente no controller traz vantagens em diversos aspectos:

- Organização do código: O controller fica responsável principalmente por coordenar o fluxo da requisição (chamar services, retornar a resource, etc), sem se preocupar com as regras de validação. Isso o deixa mais enxuto e com uma única responsabilidade, facilitando a leitura e manutenção do código, além de respeitar os padrões de SRP e DRY.

- Manutenção: Como as regras ficam centralizadas em uma classe própria, alterar uma validação (adicionar uma regra, mudar uma mensagem de erro, etc) não exige mexer no controller. Isso reduz o risco de quebrar outra parte da lógica sem querer, já que as responsabilidades estão separadas.

- Reutilização: A mesma classe de validação pode ser reaproveitada em outros pontos que recebem o mesmo tipo de dado (por exemplo, um endpoint de criação e outro de importação em lote), evitando duplicar as mesmas regras em vários controllers.

## 3 Testes Automatizados no Laravel
### Responda às seguintes perguntas:

### 1. Para que servem testes automatizados em uma aplicação Laravel?

#### Resposta:

Testes automatizados servem para garantir que a aplicação se comporta da forma esperada, e continua se comportando conforme o esperado conforme o código evolui. Eles trazem segurança para o desenvolvedor que precisa alterar o funcionamento de alguma funcionalidade identificando respostas inesperadas, servem de documentação do comportamento esperado e automatizam testes manuais, deixando o desenvolvimento mais rápido.

### 2. Caso você precise testar um endpoint da API, explique como você implementaria esse teste utilizando PHPUnit no Laravel, incluindo:

### - Onde o teste seria criado?

#### Resposta:

Testes de endpoint são testes de Feature, pois exercitam a aplicação de ponta a ponta (rota → controller → service → repository → banco), então ficam dentro da pasta de teste de Features da aplicação. São organizados por domínio assim como o restante da aplicação. Por exemplo, um teste para o endpoint de produtos fica em tests/Feature/Products/ProductTest.php.


### - Como o endpoint seria testado?

#### Resposta:

Podem ser utilizados helpers de HTTP que o Laravel já disponibiliza nos testes (getJson, postJson, putJson, deleteJson), simulando a requisição real que um cliente faria, e faria as asserções sobre a resposta (status code e corpo) e, quando fizer sentido, sobre o estado do banco.

São feitas asserções sobre as respostas que um endpoint pode retornar, verificando que uma request válida retorna dados válidos e o resultado esperado. Por exemplo, uma request de criação de produto, quando válida deve retornar uma mensagem informando que o produto foi cadastrado e verificar também que o dado foi cadastrado corretamente no banco de dados.

É de boa prática testar também não apenas casos de sucesso, mas também casos de erro, como por exemplo: 
Autenticação: Sem token de autenticação a aplicação retorna 401.
Validação: Certifica que uma request sem dados necessários recebe 422.
Autorização: Um usuário sem acesso a um certo recurso recebe 403.

Para simular o usuário autenticado, é possível utilizar o método `Sanctum::actingAs(User::factory()->create())`.
Para garantir sempre um ambiente válido de testes, após cada teste limpar banco de dados, caches, etc. Esse processo pode ser feito utilizando traits nativas ou mais específicas dependendo da implementação dos testes.


### - Como executar os testes no projeto?

#### Resposta:

A maneira mais fácil de executar os testes no projeto são as maneiras nativas do Laravel, rodando o comando:
`php artisan test` (quando o php está instalado na máquina, no caso do projeto atual que pode rodar sem o php instalado, deve rodar dentro do container docker)

Também é possível rodar só uma classe ou um teste específico, ou filtrar por uma palavra.
Esse comportamento ajuda durante o desenvolvimento, já que testes podem demorar a rodar, limitar a quantidade de testes ao fluxo em alteração, por exemplo, acelera o desenvolvimento.

`php artisan test --filter=FiltroOuNomeDaClasse`

Além de rodar em ambiente local, é possível também configurar os testes em uma pipeline que roda antes de realizar o deploy em produção, assim evitando que bugs ou regressão de funcionalidade atinjam usuários.