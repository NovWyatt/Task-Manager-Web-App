import { Schema, model } from "mongoose";

const TaskSchema = new Schema({
  title: {
    type: String,
    required: true,
  },
  completed: {
    type: Boolean,
    default: false,
  },
});

export default model("Task", TaskSchema);
