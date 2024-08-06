import React, { useState } from "react";
import axios from "axios";

const TaskForm = ({ addTask }) => {
  const [title, setTitle] = useState("");

  const handleSubmit = (e) => {
    e.preventDefault();
    axios
      .post("http://localhost:5000/tasks", { title })
      .then((response) => {
        addTask(response.data);
        setTitle("");
      })
      .catch((error) => console.error(error));
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        value={title}
        onChange={(e) => setTitle(e.target.value)}
        placeholder="New Task"
        required
      />
      <button type="submit">Add Task</button>
    </form>
  );
};

export default TaskForm;
