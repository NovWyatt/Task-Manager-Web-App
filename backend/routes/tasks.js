const express = require("express");
const router = express.Router();
const Task = require("../models/Task").default;

// Lấy tất cả các task
router.get("/", async (req, res) => {
  try {
    const tasks = await Task.find();
    res.json(tasks);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// Tạo một task mới
router.post("/", async (req, res) => {
  const task = new Task({
    title: req.body.title,
  });

  try {
    const newTask = await task.save();
    res.status(201).json(newTask);
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
});

// Cập nhật một task
router.patch("/:id", async (req, res) => {
  try {
    const task = await Task.findById(req.params.id);
    if (task == null) {
      return res.status(404).json({ message: "Cannot find task" });
    }

    if (req.body.title != null) {
      task.title = req.body.title;
    }
    if (req.body.completed != null) {
      task.completed = req.body.completed;
    }

    const updatedTask = await task.save();
    res.json(updatedTask);
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
});

// Xóa một task
router.delete("/:id", async (req, res) => {
  try {
    const result = await Task.findByIdAndDelete(req.params.id);
    if (!result) {
      return res.status(404).json({ message: "Cannot find task" });
    }
    res.json({ message: "Deleted task" });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

module.exports = router;
