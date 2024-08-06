import React, { useState } from "react";
import TaskList from "./components/TaskList";
import TaskForm from "./components/TaskForm";

const App = () => {
  const [tasks, setTasks] = useState([]);

  const addTask = (task) => {
    setTasks([...tasks, task]);
  };

  return (
    <div className="App">
      <h1>Task Manager</h1>
      <TaskForm addTask={addTask} />
      <TaskList tasks={tasks} setTasks={setTasks} />
    </div>
  );
};

export default App;
