const express = require('express');
const mysql = require('mysql2');
const bodyParser = require('body-parser');
const session = require('express-session');

const app = express();
const port = 3000;

// Middleware
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(session({
    secret: 'seu-segredo-aqui', // Troque por uma chave secreta mais segura
    resave: false,
    saveUninitialized: true
}));

// Conexão com o banco de dados
const db = mysql.createConnection({
    host: 'localhost', // ou o endereço do seu servidor MySQL
    user: 'root', // seu usuário do MySQL
    password: '------', // sua senha do MySQL
    database: 'seu_banco_de_dados' // o nome do seu banco de dados
});

// Conectar ao banco de dados
db.connect((err) => {
    if (err) {
        console.error('Erro ao conectar ao banco de dados:', err);
        return;
    }
    console.log('Conectado ao banco de dados MySQL!');
});

// Rota para processar o login
app.post('/login', (req, res) => {
    const { email, password } = req.body;

    // Verifique se o usuário existe no banco de dados
    db.query('SELECT * FROM users WHERE email = ? AND password = ?', [email, password], (err, results) => {
        if (err) {
            console.error(err);
            return res.status(500).json({ message: 'Erro ao consultar o banco de dados' });
        }

        if (results.length > 0) {
            // Login bem-sucedido
            req.session.userId = results[0].id; // Armazena o ID do usuário na sessão
            res.json({ message: 'Login bem-sucedido!' });
        } else {
            // Login falhou
            res.status(401).json({ message: 'Usuário ou senha incorretos.' });
        }
    });
});

// Rota para processar o cadastro
app.post('/cadastro', (req, res) => {
    const { email, password } = req.body;

    // Verifique se o usuário já existe
    db.query('SELECT * FROM users WHERE email = ?', [email], (err, results) => {
        if (err) {
            console.error(err);
            return res.status(500).json({ message: 'Erro ao consultar o banco de dados' });
        }

        if (results.length > 0) {
            // Usuário já existe
            return res.status(409).json({ message: 'Usuário já existe.' });
        }

        // Insira o novo usuário no banco de dados
        db.query('INSERT INTO users (email, password) VALUES (?, ?)', [email, password], (err, results) => {
            if (err) {
                console.error(err);
                return res.status(500).json({ message: 'Erro ao cadastrar o usuário' });
            }
            res.json({ message: 'Cadastro bem-sucedido!' });
        });
    });
});

// Iniciar o servidor
app.listen(port, () => {
    console.log(`Servidor rodando em http://localhost:${port}`);
});