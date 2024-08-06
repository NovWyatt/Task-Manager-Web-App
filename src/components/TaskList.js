import React, { useEffect, useState } from "react";
import axios from "axios";
import Task from "./Task";

const TaskList = () => {
  const [tasks, setTasks] = useState([]);

  useEffect(() => {
    axios
      .get("http://localhost:5000/tasks")
      .then((response) => setTasks(response.data))
      .catch((error) => console.error(error));
  }, []);

  const toggleComplete = (id) => {
    const task = tasks.find((task) => task._id === id);
    axios
      .patch(`http://localhost:5000/tasks/${id}`, {
        completed: !task.completed,
      })
      .then((response) =>
        setTasks(tasks.map((t) => (t._id === id ? response.data : t)))
      )
      .catch((error) => console.error(error));
  };

  const deleteTask = (id) => {
    axios
      .delete(`http://localhost:5000/tasks/${id}`)
      .then(() => setTasks(tasks.filter((task) => task._id !== id)))
      .catch((error) => console.error(error));
  };

  return (
    <div>
      {tasks.map((task) => (
        <Task
          key={task._id}
          task={task}
          toggleComplete={toggleComplete}
          deleteTask={deleteTask}
        />
      ))}
    </div>
  );
};

export default TaskList;
