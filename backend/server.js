const express = require("express");
const mongoose = require("mongoose");
const cors = require("cors");
const bodyParser = require("body-parser");

const app = express();
app.use(cors());
app.use(bodyParser.json());

// Kết nối tới MongoDB
mongoose.connect("mongodb://localhost/todo-app", {});

// Xử lý kết nối cơ sở dữ liệu
const db = mongoose.connection;
db.on("error", (error) => console.error(error));
db.once("open", () => console.log("Connected to Database"));

const tasksRouter = require("./routes/tasks");
app.use("/tasks", tasksRouter);

app.listen(5000, () => console.log("Server Started on port 5000"));
