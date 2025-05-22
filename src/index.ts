import express, { Request, Response } from 'express';
import bodyParser from 'body-parser';
import cors from 'cors';
import mysql, { Connection } from 'mysql2';
import { RowDataPacket } from 'mysql2';



// Inicializa o app
const app = express();
app.use(cors());
app.use(bodyParser.json());

const db: Connection = mysql.createConnection({
    host: '127.0.0.1',    // força TCP, evita ambiguidades de 'localhost'
    port: 3306,           // porta padrão do MySQL
    user: 'root',         // seu usuário
    password: '',         // sua senha
    database: 'grafico', // seu database existente
    connectTimeout: 10000 // 10 segundos antes de dar timeout
  });

// Teste de conexão
db.connect((err) => {
    if (err) throw err;
    console.log('Conectado ao MySQL!');
});

// CREATE
app.post('/dados', (req: Request, res: Response) => {
    const { nome, action, time } = req.body;
    const sql = 'INSERT INTO dados (nome, action, time) VALUES (?, ?, ?)';
    db.query(sql, [nome, action, time], (err, result: any) => {
        if (err) throw err;
        res.send({ id: result.insertId, nome, action, time });
    });
});

// READ ALL
app.get('/dados', (_req: Request, res: Response) => {
    db.query('SELECT * FROM dados', (err, results) => {
        if (err) throw err;
        res.send(results);
    });
});

// READ by ID
app.get('/dados/:id', (req: Request, res: Response) => {
    const id = req.params.id;
    const sql = 'SELECT * FROM dados WHERE id = ?';

    db.query<RowDataPacket[]>(sql, [id], (err, results) => {
        if (err) return res.status(500).send(err);
        if (results.length === 0) return res.status(404).send({ message: 'Registro não encontrado' });
        res.send(results[0]);
    });
});
// UPDATE
app.put('/dados/:id', (req: Request, res: Response) => {
    const id = req.params.id;
    const { nome, action, time } = req.body;
    const sql = 'UPDATE dados SET nome = ?, action = ?, time = ? WHERE id = ?';
    db.query(sql, [nome, action, time, id], (err) => {
        if (err) throw err;
        res.send({ id, nome, action, time });
    });
});

// DELETE
app.delete('/dados/:id', (req: Request, res: Response) => {
    const id = req.params.id;
    db.query('DELETE FROM dados WHERE id = ?', [id], (err) => {
        if (err) throw err;
        res.send({ message: `Registro ${id} removido.` });
    });
});


// Inicia o servidor
app.listen(3000, () => {
    console.log('API TypeScript rodando em http://localhost:3000');
});
