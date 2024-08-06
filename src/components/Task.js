import React from "react";

const Task = ({ task, toggleComplete, deleteTask }) => {
  return (
    <div className="task">
      <span
        style={{ textDecoration: task.completed ? "line-through" : "none" }}
      >
        {task.title}
      </span>
      <button onClick={() => toggleComplete(task._id)}>
        {task.completed ? "Incomplete" : "Complete"}
      </button>
      <button onClick={() => deleteTask(task._id)}>Delete</button>
    </div>
  );
};

export default Task;
